<?php

namespace MINSAL\IndicadoresBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CargarOrigenDatoConsumer implements ConsumerInterface {

    protected $container;
    protected $numMsj = 1;

    public function __construct(ContainerInterface $container) {
        $this->container = $container;
    }

    public function execute(AMQPMessage $msg) {
        $msg = unserialize($msg->body);
        $em = $this->container->get('doctrine.orm.entity_manager');

        $this->numMsj = 1;
        $idOrigen = $msg['id_origen_dato'];
        $origenDato = $em->find('IndicadoresBundle:OrigenDatos', $idOrigen);

        $campos_sig = $msg['campos_significados'];

        $fecha = new \DateTime("now");
        $ahora = $fecha->format('Y-m-d H:i:s');

        echo '
            ========== INICIO CARGA=========== '. $origenDato .' TIEMPO: '. microtime(true).' 
            ';
        
        try {
            //Leeré los datos en grupos de 5,000
            $tamanio = 5000;

            if ($origenDato->getSentenciaSql() != '') {
                //$sql = $origenDato->getSentenciaSql();
                $sql = $msg['sql'];

                foreach ($origenDato->getConexiones() as $cnx) {
                    $leidos = $tamanio + 1;
                    $i = 0;
                    echo '

                    *********************************************************************************************
                    *********************************************************************************************
                    Conexion '.$cnx.' '.microtime(true).' Origen: '.$idOrigen.' 
                        ';
                    $lect = 1;
                    $datos = true;
                    $this->enviarMsjInicio($idOrigen);
                    while ($leidos >= $tamanio and $datos != false) {
                        if ($cnx->getIdMotor()->getCodigo() == 'oci8') {
                            $sql_aux = ($msg['esLecturaIncremental']) ?
                                    "SELECT * FROM ( $sql )  sqlOriginal 
                                        WHERE  1 = 1
                                            $msg[condicion_carga_incremental]
                                            AND ROWNUM >= " . $i * $tamanio . ' AND ROWNUM < ' . ($tamanio * ($i + 1)) .
                                    $msg[orden] :
                                    'SELECT * FROM (' . $sql . ')  sqlOriginal ' .
                                    'WHERE ROWNUM >= ' . $i * $tamanio . ' AND ROWNUM < ' . ($tamanio * ($i + 1));
                        } elseif ($cnx->getIdMotor()->getCodigo() == 'pdo_dblib') {
                            $sql_aux = ($msg['esLecturaIncremental']) ?
                                    "SELECT * FROM ( $sql )  sqlOriginal 
                                        WHERE 1 = 1
                                        $msg[condicion_carga_incremental]
                                        $msg[orden] " : $sql;
                        } else {
                            $sql_aux = ($msg['esLecturaIncremental']) ?
                                    "SELECT * FROM ( $sql) sqlOriginal 
                                            WHERE 1 = 1
                                            $msg[condicion_carga_incremental]
                                            $msg[orden]
                                            LIMIT " . $tamanio . ' OFFSET ' . $i * $tamanio :
                                    $sql . ' LIMIT ' . $tamanio . ' OFFSET ' . $i * $tamanio;
                            ;
                        }
                        echo '   

                                    +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
                                    ---> Lectura de datos '. $lect . ' iniciada en '. microtime(true) . ' Origen: '.$idOrigen.' 
                            ';
                        
                        $datos = $em->getRepository('IndicadoresBundle:OrigenDatos')->getDatos($sql_aux, $cnx);

                        echo '     
                                    ---> Lectura de datos '. $lect . ' FINALIZADA EN '. microtime(true) . ' Origen: '.$idOrigen.' 
                            ';

                        if ($datos === false){
                            $leidos = 1;
                            echo '
                                    SIN REGISTROS  ---> Origen: '.$idOrigen.' 

                            ' ;
                        } else {
                            $this->enviarDatos($idOrigen, $datos, $campos_sig, $ahora, $cnx->getId());
                            if ($cnx->getIdMotor()->getCodigo() == 'pdo_dblib')
                                $leidos = 1;
                            else
                                $leidos = count($datos);
                            $i++;
                            echo '
                                     Envio '. $lect. ' Cantidad de registros :' . $leidos.' Origen: '.$idOrigen.' 

                            ' ;
                        }
                        $lect++;

                    }                    
                    
                    $this->enviarMsjFinal($msg, $idOrigen, $ahora, $cnx->getId());
                }
            } else {
                $datos = $em->getRepository('IndicadoresBundle:OrigenDatos')->getDatos(null, null, $origenDato->getAbsolutePath());
                $this->enviarMsjInicio($idOrigen);
                
                $this->enviarDatos($idOrigen, $datos, $campos_sig, $ahora, $cnx->getId(), null);
                
                $this->enviarMsjFinal($msg, $idOrigen, $ahora, null);
            }
            

            echo '
                ==========FIN DE CARGA=========== '. $origenDato .' TIEMPO: '. microtime(true).' Origen: '.$idOrigen.' 
                    ULTIMO MENSAJE # '. $this->numMsj.' 
                ';            

            $origenDato->setUltimaActualizacion($fecha);
            $em->flush();
        } catch (\Exception $e) {
            echo 'CODC 1' . $e->getMessage();
        } catch (\ErrorException $e) {
            echo 'CODC 1' . $e->getMessage();
        }
        return true;
    }
    
    private function enviarMsjFinal ($msg, $idOrigen, $ahora, $idConexion) {
        //Después de enviados todos los registros para guardar, mandar mensaje para borrar los antiguos
        $msg_guardar = array('id_origen_dato' => $idOrigen,
            'method' => 'DELETE',
            'ultima_lectura' => $ahora,
            'es_lectura_incremental' => $msg['esLecturaIncremental'],
            'lim_inf' => $msg['lim_inf'],
            'lim_sup' => $msg['lim_sup'],
            'id_conexion' =>$idConexion,
            'campo_lectura_incremental' => $msg['campoLecturaIncremental'],
            'r' => microtime(true),
            'numMsj' => $this->numMsj++
        );

        $this->container->get('old_sound_rabbit_mq.guardar_registro_producer')
            ->publish(json_encode($msg_guardar));
    }
    
    private function enviarMsjInicio ($idOrigen) {
        $msg_init = array('id_origen_dato' => $idOrigen,
                'method' => 'BEGIN',
                'r' => microtime(true),
                'numMsj' => $this->numMsj++
            );
            $this->container->get('old_sound_rabbit_mq.guardar_registro_producer')
                    ->publish(json_encode($msg_init));
    }

    public function enviarDatos($idOrigen, $datos, $campos_sig, $ultima_lectura, $idConexion) {
        //Esta cola la utilizaré solo para leer todos los datos y luego mandar uno por uno
        // a otra cola que se encarará de guardarlo en la base de datos
        // luego se puede probar a mandar por grupos
        $datos_a_enviar = array();
        $util = new \MINSAL\IndicadoresBundle\Util\Util();
        $i = 0;
        $ii = 0;
        $grpMsj = 1;

        if ( count($datos) > 0 ) {
            //Cambiar el nombre de las llaves, en lugar de usar el nombre del campo
            // se usará el nombre del significado
            $datos_a_enviar = array();
            foreach ($datos as $fila) {
                $nueva_fila = array();
                foreach ($fila as $k => $v) {
                    $nueva_fila[$campos_sig[$util->slug($k)]] = trim(mb_check_encoding($v, 'UTF-8') ? $v : utf8_encode($v));
                }
                $datos_a_enviar[] = $nueva_fila;
            }
            
            $msg_guardar = array('id_origen_dato' => $idOrigen,
                'method' => 'PUT',
                'datos' => $datos_a_enviar,
                'ultima_lectura' => $ultima_lectura,
                'id_conexion' => $idConexion,
                'r' => microtime(true),
                'numMsj' => $this->numMsj++
            );
            
            $msg = json_encode($msg_guardar);
            
            try {
                $this->container->get('old_sound_rabbit_mq.guardar_registro_producer')
                        ->publish($msg);
            } catch (\Exception $e) {
                echo $e->getMessage();
            }
            
            
        }
        echo ' 
            ';
    }

}