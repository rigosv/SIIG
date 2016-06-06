<?php

namespace MINSAL\IndicadoresBundle\Consumer;

use OldSound\RabbitMqBundle\RabbitMq\ConsumerInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Doctrine\ORM\EntityManager;

class GuardarRegistroOrigenDatoConsumer implements ConsumerInterface
{
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    public function execute(AMQPMessage $mensaje)
    {
        $msg = unserialize($mensaje->body);
        
        //Verificar si tiene código de costeo
        $sql = "SELECT area_costeo FROM origen_datos WHERE id = $msg[id_origen_dato]";
        $areaCosteo =  $this->em->getConnection()->executeQuery($sql)->fetch();
            
        // Si se retorna falso se enviará un mensaje que le indicará al producer que no se pudo procesar
        // correctamente el mensaje y será enviado nuevamente
        if ($msg['method'] == 'PUT') {
            $fila1 = $msg['datos'][0];                       

            $llaves_aux1 = '';
            foreach ($fila1 as $k => $campo)
                $llaves_aux1 .= "'$k', ";
            $llaves_aux1 = trim($llaves_aux1, ', ');
            
            $sql = "INSERT INTO fila_origen_dato_aux(id_origen_dato, datos, ultima_lectura)
                    VALUES ";
            $i = 0;
            foreach ($msg['datos'] as $fila) {
                $llaves_aux2 = '';
                foreach ($fila as $k => $campo)
                    $llaves_aux2 .= ":$k" . "_$i, ";
                $llaves_aux2 = trim($llaves_aux2, ', ');

                $sql .= "(:id_origen_dato, hstore(ARRAY[$llaves_aux1], ARRAY[$llaves_aux2]), :ultima_lectura), ";
                $i++;
            }
            $sql = trim($sql, ', ');
            $sth = $this->em->getConnection()->prepare($sql);
            $sth->bindParam(':id_origen_dato', $msg['id_origen_dato']);
            $sth->bindParam(':ultima_lectura', $msg['ultima_lectura']);

            $this->em->getConnection()->beginTransaction();
            $i = 0;
            foreach ($msg['datos'] as $fila) {
                foreach ($fila as $k => $value) {
                    $llave = ':' . $k . '_' . $i;
                    $sth->bindValue("$llave", trim($value));
                }
                $i++;
            }
            $result = $sth->execute();
            if (!$result)
                return false;
            $this->em->getConnection()->commit();

            return true;
        } elseif ($msg['method'] == 'DELETE') {
            $tabla = ($areaCosteo['area_costeo'] == '') ? 'origenes.fila_origen_dato_'.$msg['id_origen_dato'] : 'costos.fila_origen_dato_' . $areaCosteo['area_costeo'];
            //verificar si la tabla existe
            if ($tabla == 'origenes.fila_origen_dato_'.$msg['id_origen_dato']){
                try {
                    $this->em->getConnection()->query("select * from $tabla LIMIT 1");
                } catch (\Doctrine\DBAL\DBALException $e) {
                    //Crear la tabla
                    $this->em->getConnection()->exec("select * INTO $tabla from fila_origen_dato_aux LIMIT 0 ");
                }
            }
            
            $this->em->getConnection()->beginTransaction();
            
            if ($areaCosteo['area_costeo'] == 'rrhh'){
                //Solo agregar los datos nuevos
                $sql = " INSERT INTO $tabla 
                            SELECT *  FROM fila_origen_dato_aux 
                            WHERE id_origen_dato='$msg[id_origen_dato]'
                                AND datos->'nit' 
                                    NOT IN 
                                    (SELECT datos->'nit' FROM $tabla); 
                        DELETE FROM fila_origen_dato_aux WHERE id_origen_dato='$msg[id_origen_dato]'
                         ";
            } elseif ($areaCosteo['area_costeo'] == 'ga_af'){
                //Solo agregar los datos nuevos
                $sql = " INSERT INTO $tabla 
                            SELECT *  FROM fila_origen_dato_aux 
                            WHERE id_origen_dato='$msg[id_origen_dato]'
                                AND datos->'codigo_af' 
                                    NOT IN 
                                    (SELECT datos->'codigo_af' FROM $tabla); 
                        DELETE FROM fila_origen_dato_aux WHERE id_origen_dato='$msg[id_origen_dato]'
                        ";
            } else {
                if ($msg['es_lectura_incremental']){
                    $sql = "DELETE 
                                FROM $tabla 
                                WHERE id_origen_dato='$msg[id_origen_dato]'  
                                    AND datos->'$msg[campo_lectura_incremental]' >= '$msg[lim_inf]'
                                    AND datos->'$msg[campo_lectura_incremental]' <= '$msg[lim_sup]'
                                    ;
                        INSERT INTO $tabla SELECT * FROM fila_origen_dato_aux WHERE id_origen_dato='$msg[id_origen_dato]';
                        DELETE FROM fila_origen_dato_aux WHERE id_origen_dato='$msg[id_origen_dato]' ;
                        ";
                }
                else {
                    $sql = "DELETE FROM $tabla WHERE id_origen_dato='$msg[id_origen_dato]'  ;
                        INSERT INTO $tabla SELECT * FROM fila_origen_dato_aux WHERE id_origen_dato='$msg[id_origen_dato]';
                        DELETE FROM fila_origen_dato_aux WHERE id_origen_dato='$msg[id_origen_dato]' ;
                        ";
                }
            }
            $this->em->getConnection()->exec($sql);
            $this->em->getConnection()->commit();

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
            }*/

            return true;
        }
    }

}
