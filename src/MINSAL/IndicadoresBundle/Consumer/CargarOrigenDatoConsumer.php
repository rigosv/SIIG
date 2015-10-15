<?php

namespace MINSAL\IndicadoresBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CargarOrigenDatoConsumer implements ConsumerInterface
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function execute(AMQPMessage $msg)
    {
        $msg = unserialize($msg->body);
        $em = $this->container->get('doctrine.orm.entity_manager');

        $idOrigen = $msg['id_origen_dato'];
        $origenDato = $em->find('IndicadoresBundle:OrigenDatos', $idOrigen);

        $campos_sig = $msg['campos_significados'];

        $fecha = new \DateTime("now");
        $ahora = $fecha->format('Y-m-d H:i:s');

        //Leeré los datos en grupos de 10,000
        $tamanio = 10000;
        
        if ($origenDato->getSentenciaSql() != '') {
            //$sql = $origenDato->getSentenciaSql();
            $sql = $msg['sql'];
            foreach ($origenDato->getConexiones() as $cnx) {
                $leidos = 10001;
                $i = 0;
                while ($leidos >= $tamanio) {
                    if ($cnx->getIdMotor()->getCodigo() == 'oci8' ) {
                        $sql_aux = ($msg['esLecturaIncremental']) ? 
                                "SELECT * FROM ( $sql )  sqlOriginal 
                                    WHERE  1 = 1
                                        $msg[condicion_carga_incremental]
                                        AND ROWNUM >= " . $i * $tamanio . ' AND ROWNUM < '.  ($tamanio * ($i + 1)).
                                    $msg[orden]
                                :
                                'SELECT * FROM (' . $sql . ')  sqlOriginal '.
                            'WHERE ROWNUM >= ' . $i * $tamanio . ' AND ROWNUM < '.  ($tamanio * ($i + 1));
                        
                    }elseif($cnx->getIdMotor()->getCodigo() == 'pdo_dblib'){
                        $sql_aux = ($msg['esLecturaIncremental']) ? 
                                "SELECT * FROM ( $sql )  sqlOriginal 
                                    WHERE 1 = 1
                                    $msg[condicion_carga_incremental]
                                    $msg[orden] "
                                :  $sql;                       
                    }
                    else {
                        $sql_aux = ($msg['esLecturaIncremental']) ? 
                                    "SELECT * FROM ( $sql) sqlOriginal 
                                        WHERE 1 = 1
                                        $msg[condicion_carga_incremental]
                                        $msg[orden]
                                        LIMIT " . $tamanio . ' OFFSET ' . $i * $tamanio 
                                    :
                                    $sql . ' LIMIT ' . $tamanio . ' OFFSET ' . $i * $tamanio;;
                    }
                    
                    $datos = $em->getRepository('IndicadoresBundle:OrigenDatos')->getDatos($sql_aux, $cnx);

                    $this->enviarDatos($idOrigen, $datos, $campos_sig, $ahora);
                    if ($cnx->getIdMotor()->getCodigo() == 'pdo_dblib')
                        $leidos = 1;
                    else $leidos = count($datos);
                    $i++;
                }                
            } 
        } else {
            $datos = $em->getRepository('IndicadoresBundle:OrigenDatos')->getDatos(null, null, $origenDato->getAbsolutePath());
            $this->enviarDatos($idOrigen, $datos, $campos_sig, $ahora);
        }
        //Después de enviados todos los registros para guardar, mandar mensaje para borrar los antiguos
        $msg_guardar = array('id_origen_dato' => $idOrigen,
            'method' => 'DELETE',
            'ultima_lectura' => $ahora,
            'es_lectura_incremental' => $msg['esLecturaIncremental'],
            'lim_inf'=> $msg['lim_inf'],
            'lim_sup'=> $msg['lim_sup'],
            'ultima_lectura_incremental' => $msg['ultimaLecturaIncremental'],            
            'campo_lectura_incremental' => $msg['campoLecturaIncremental']
        );
        $this->container->get('old_sound_rabbit_mq.guardar_registro_producer')
                ->publish(serialize($msg_guardar));
                
        $origenDato->setUltimaActualizacion($fecha);
        $em->flush();
        return true;
    }

    public function enviarDatos($idOrigen, $datos, $campos_sig, $ultima_lectura)
    {
        //Esta cola la utilizaré solo para leer todos los datos y luego mandar uno por uno
        // a otra cola que se encarará de guardarlo en la base de datos
        // luego se puede probar a mandar por grupos
        $datos_a_enviar = array();
        $util = new \MINSAL\IndicadoresBundle\Util\Util();
        $i = 0;
        $ii = 0;
        if ($datos) {
            foreach ($datos as $fila) {
                $nueva_fila = array();
                foreach ($fila as $k => $v) {
                    // Quitar caracteres no permitidos que podrian existir en el nombre de campo (tildes, eñes, etc)
                    //Verificar si ya está en UTF-8, si no, codificarlo
                    $nueva_fila[$campos_sig[$util->slug($k)]] = trim(mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v));
                }
                $datos_a_enviar[] = $nueva_fila;
                //Enviaré en grupos de 200
                if ($i == 200) {
                    $msg_guardar = array('id_origen_dato' => $idOrigen,
                        'method' => 'PUT',
                        'datos' => $datos_a_enviar,
                        'ultima_lectura' => $ultima_lectura,
                        'num_msj' => $ii++);
                    $this->container->get('old_sound_rabbit_mq.guardar_registro_producer')
                            ->publish(serialize($msg_guardar));
                    unset($datos_a_enviar);
                    $datos_a_enviar = array();
                    $i = 0;
                }
                $i++;
            }
        }
        //Verificar si quedaron pendientes de enviar
        if (count($datos_a_enviar) > 0) {
            $msg_guardar = array('id_origen_dato' => $idOrigen,
                'method' => 'PUT',
                'datos' => $datos_a_enviar,
                'ultima_lectura' => $ultima_lectura,
                'num_msj' => $ii++);
            $this->container->get('old_sound_rabbit_mq.guardar_registro_producer')
                    ->publish(serialize($msg_guardar));
        }
    }

}
