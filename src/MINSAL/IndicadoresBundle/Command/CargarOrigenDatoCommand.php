<?php

namespace MINSAL\IndicadoresBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CargarOrigenDatoCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
                ->setName('origen-dato:cargar')
                ->setDescription('Cargar datos especificados en los orígenes')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();

        //Recuperar todos las fichas técnicas de indicadores
        $indicadores = $em->getRepository('IndicadoresBundle:FichaTecnica')->findAll();
        
        $fecha = new \DateTime("now");
        $ahora = $fecha;        

        foreach ($indicadores as $ind) {

            if ($ind->getUltimaLectura() == null)
                $dif = 1; // No se ha realizado carga de datos antes, mandar a cargarlos
            else {
                $ultima_lectura = $ind->getUltimaLectura();
                if ($ind->getPeriodo() != null)
                    $periocidad = $ind->getPeriodo()->getCodigo();
                else $periocidad='d';

                $intervalo = $ahora->diff($ultima_lectura);
                $dif_dias = $intervalo->format('%a');

                if ($periocidad == 'd') //Diario?
                    $dif = $dif_dias;
                elseif ($periocidad == 'sm') //semanal?
                    $dif = $dif_dias / 7;
                elseif ($periocidad == 'm')
                    $dif = $dif_dias / 30; //mensual?
                elseif ($periocidad == 't')
                    $dif = $dif_dias / 30 * 3; //trimestral?
                elseif ($periocidad == 's')
                    $dif = $dif_dias / 30 * 6; //semestral?
                elseif ($periocidad == 'a')
                    $dif = $dif_dias / 365; //Anual?
                else
                    $dif = 1; // No tiene periocidad, cargarlo
            }
            if ($dif >= 1) {
                //Es necesaria realizar la carga de datos
                // Recuperar los orígenes de datos asociados a las variables del indicador
                foreach ($ind->getVariables() as $var) {
                    $origenDato = $var->getOrigenDatos();
                    // Solo los orígenes desde base de datos se cargarán periodicamente
                    // los que sean de archivos solo a demanda 
                    if ($origenDato->getSentenciaSql() != '') {
                        // Recuperar el nombre y significado de los campos del origen de datos
                        $campos_sig = array();
                        $campos = $origenDato->getCampos();
                        foreach ($campos as $campo) {
                            $campos_sig[$campo->getNombre()] = $campo->getSignificado()->getCodigo();
                        }

                        //Verificar si elorigen de datos tiene un campo para lectura incremental
                        $campoLecturaIncremental = $origenDato->getCampoLecturaIncremental();
                        $condicion_carga_incremental = "";
                        $ultimaLecturaIncremental = null;
                        $esLecturaIncremental = ($campoLecturaIncremental == null) ? false: true;
                        $orden = " ";
                        $lim_inf= '';
                        $lim_sup =  '';
                        if ($esLecturaIncremental){
                            //tomar la fecha de la última actualización del origen
                            $campoLecturaIncremental = $campoLecturaIncremental->getSignificado()->getCodigo();
                            $ultimaLecturaIncremental = $origenDato->getUltimaActualizacion();
                            if ($ultimaLecturaIncremental != null ){
                               //Ya se realizó al menos una carga, a partir de la segunda leer solo el incremento
                                $ventana_inf = ($origenDato->getVentanaLimiteInferior() == null) ? 0 : $origenDato->getVentanaLimiteInferior();
                                $ventana_sup = ($origenDato->getVentanaLimiteSuperior() == null) ? 0 : $origenDato->getVentanaLimiteSuperior();

                                if ($campoLecturaIncremental == 'fecha'){                
                                    $ultimaLecturaIncremental->sub(new \DateInterval('P'.$ventana_inf.'D'));
                                    $fecha->sub(new \DateInterval('P'.$ventana_sup.'D'));
                                    $lim_inf= $ultimaLecturaIncremental->format('Y-m-d H:i:s');
                                    $lim_sup = $fecha->format('Y-m-d H:i:s');

                                } else {
                                    // Se está utilizando el campo año para la carga incremental
                                    $lim_inf = $ultimaLecturaIncremental->format('Y') - $ventana_inf ;
                                    $lim_sup = $fecha->format('Y') - $ventana_sup;
                                }
                                $condicion_carga_incremental = " AND $campoLecturaIncremental >= '$lim_inf'
                                                                     AND $campoLecturaIncremental <= '$lim_sup' ";
                            }
                            $orden = " ORDER BY $campoLecturaIncremental ";
                            $ultimaLecturaIncremental = $ultimaLecturaIncremental->format('Y-m-d H:i:s');
                        }
                        $msg = array('id_origen_dato' => $origenDato->getId(), 
                                    'sql' => $origenDato->getSentenciaSql(),
                                    'campos_significados' => $campos_sig,
                                    'lim_inf' => $lim_inf,
                                    'lim_sup' => $lim_sup,
                                    'condicion_carga_incremental' => $condicion_carga_incremental,
                                    'orden' => $orden,
                                    'esLecturaIncremental' => $esLecturaIncremental,
                                    'campoLecturaIncremental' => $campoLecturaIncremental,
                                    'ultimaLecturaIncremental' => $ultimaLecturaIncremental,
                                    'r' => microtime(true) 

                            );                                        

                        $carga_directa = $origenDato->getEsCatalogo();
                        // No mandar a la cola de carga los que son catálogos, Se cargarán directamente
                        if ($carga_directa)
                            $em->getRepository('IndicadoresBundle:OrigenDatos')->cargarCatalogo($origenDato);
                        else
                            $this->getContainer()->get('old_sound_rabbit_mq.cargar_origen_datos_producer')
                                    ->publish(serialize($msg));
                        $ind->setUltimaLectura($ahora);
                    }
                }
            }
        }
        $em->flush();
    }

}
