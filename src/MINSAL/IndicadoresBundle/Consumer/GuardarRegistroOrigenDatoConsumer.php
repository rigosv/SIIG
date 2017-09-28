<?php

namespace MINSAL\IndicadoresBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Doctrine\ORM\EntityManager;

class GuardarRegistroOrigenDatoConsumer implements ConsumerInterface {

    protected $em;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function execute(AMQPMessage $mensaje) {
        $msg = unserialize(base64_decode($mensaje->body));
        echo '  Msj: '. $msg['id_origen_dato']. '/'. $msg['numMsj'] . '  ';

        //Verificar si tiene código de costeo
        $sql = "SELECT area_costeo FROM origen_datos WHERE id = $msg[id_origen_dato]";
        $areaCosteo = $this->em->getConnection()->executeQuery($sql)->fetch();
        
        $tabla = ($areaCosteo['area_costeo'] == '') ? 'origenes.fila_origen_dato_' . $msg['id_origen_dato'] : 'costos.fila_origen_dato_' . $areaCosteo['area_costeo'];

        if ($msg['method'] == 'BEGIN') {
            // Iniciar borrando los datos que pudieran existir en la tabla auxiliar
            $sql = ' DROP TABLE IF EXISTS '.$tabla.'_tmp ;
                SELECT * INTO '.$tabla."_tmp FROM fila_origen_dato_v2 LIMIT 0;
                UPDATE origen_datos SET carga_finalizada = false WHERE id = '$msg[id_origen_dato]'
               ";
            $this->em->getConnection()->exec($sql);
            return true;
            
        } elseif ($msg['method'] == 'PUT') {            
            
            $sql = "INSERT INTO $tabla"."_tmp(id_origen_dato, datos, ultima_lectura)
                    VALUES ($msg[id_origen_dato], :datos, '$msg[ultima_lectura]') ";
            $sth = $this->em->getConnection()->prepare($sql);
            //$i = 0;
            echo '(inicio: '.microtime(true);
            foreach ($msg['datos'] as $fila) {
                $filaJson = json_encode($fila);
                $sth->bindParam(':datos', $filaJson);
                try {
                    $sth->execute();
                } catch (\Doctrine\DBAL\DBALException $e) {
                    echo $e->getMessage();
                    return false;
                }
                
            }
            echo ' - fin: '.microtime(true) . ') ****';
            return true;
            
        } elseif ($msg['method'] == 'DELETE') {            
            //verificar si la tabla existe
            if ($tabla == 'origenes.fila_origen_dato_' . $msg['id_origen_dato']) {
                try {
                    $this->em->getConnection()->query("select * from $tabla LIMIT 1");
                } catch (\Doctrine\DBAL\DBALException $e) {
                    //Crear la tabla
                    $this->em->getConnection()->exec("select * INTO $tabla from $tabla"."_tmp LIMIT 0 ");
                }
            }

            //$this->em->getConnection()->beginTransaction();

            if ($areaCosteo['area_costeo'] == 'rrhh') {
                //Solo agregar los datos nuevos
                $sql = " INSERT INTO $tabla 
                            SELECT *  FROM $tabla"."_tmp 
                            WHERE id_origen_dato='$msg[id_origen_dato]'
                                AND datos->'nit' 
                                    NOT IN 
                                    (SELECT datos->'nit' FROM $tabla); 
                        DELETE FROM fila_origen_dato_aux WHERE id_origen_dato='$msg[id_origen_dato]'
                         ";
            } elseif ($areaCosteo['area_costeo'] == 'ga_af') {
                //Solo agregar los datos nuevos
                $sql = " INSERT INTO $tabla 
                            SELECT *  FROM fila_origen_dato_aux 
                            WHERE id_origen_dato='$msg[id_origen_dato]'
                                AND datos->'codigo_af' 
                                    NOT IN 
                                    (SELECT datos->'codigo_af' FROM $tabla); 
                        DROP TABLE IF EXISTS ".$tabla.'_tmp; ';
            } else {
                if ($msg['es_lectura_incremental']) {
                    $sql = "DELETE 
                                FROM $tabla 
                                WHERE id_origen_dato='$msg[id_origen_dato]'  
                                    AND datos->'$msg[campo_lectura_incremental]' >= '$msg[lim_inf]'
                                    AND datos->'$msg[campo_lectura_incremental]' <= '$msg[lim_sup]'
                                    ;
                        INSERT INTO $tabla SELECT * FROM $tabla"."_tmp WHERE id_origen_dato='$msg[id_origen_dato]';
                        DROP TABLE IF EXISTS ".$tabla.'_tmp ;';
                        
                } else {
                    //Borrar los datos anteriores
                    $sql = "DROP TABLE IF EXISTS $tabla ;";
                    $this->em->getConnection()->exec($sql);

                    $sql = "SELECT * INTO $tabla FROM $tabla"."_tmp WHERE id_origen_dato='$msg[id_origen_dato]' ";
                    $this->em->getConnection()->exec($sql);
                    /*        
                    $tamanio = 100000;
                    $totalReg = 0;
                    $leidos = 1;
                    $i = 0;
                    while ($leidos > 0) {
                        $sql_aux = $sql . ' LIMIT ' . $tamanio . ' OFFSET ' . $i * $tamanio;
                        $leidos = $this->em->getConnection()->exec($sql_aux);
                        $i++;
                    }*/
                    $sql = ' DROP TABLE IF EXISTS '.$tabla.'_tmp ';
                    //$sql = "
                      //  DELETE FROM fila_origen_dato_aux WHERE id_origen_dato='$msg[id_origen_dato]' ;
                        //";
                }
            }
            $this->em->getConnection()->exec($sql);
            
            $inicio = new \DateTime($msg['ultima_lectura']);
            $fin = new \DateTime("now");
            $diffInSeconds = $fin->getTimestamp() - $inicio->getTimestamp();
            $sql = "UPDATE origen_datos SET tiempo_segundos_ultima_carga = $diffInSeconds WHERE id = '$msg[id_origen_dato]';
                    UPDATE origen_datos SET carga_finalizada = true WHERE id = '$msg[id_origen_dato]'";
            $this->em->getConnection()->exec($sql);
            
            echo '
Carga finalizada de origen ' . $msg['id_origen_dato'] . '

';
            //$this->em->getConnection()->commit();

            /* Mover esto a otro lugar más adecuado, aquí hace que la carga de los indicadores tarde mucho
              //Recalcular la tabla del indicador
              //Recuperar las variables en las que está presente el origen de datos
              $origenDatos = $this->em->find('IndicadoresBundle:OrigenDatos', $msg['id_origen_dato']);
              foreach ($origenDatos->getVariables() as $var) {
              foreach ($var->getIndicadores() as $ind) {
              $fichaTec = $this->em->find('IndicadoresBundle:FichaTecnica', $ind->getId());
              $fichaRepository = $this->em->getRepository('IndicadoresBundle:FichaTecnica');
              $fichaRepository->crearCamposIndicador($fichaTec);
              if (!$fichaTec->getEsAcumulado())
              $fichaRepository->crearIndicador($fichaTec);
              }
              } */

            return true;
        }
    }

}
