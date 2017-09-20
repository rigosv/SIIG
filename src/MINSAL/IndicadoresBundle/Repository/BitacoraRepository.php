<?php

namespace MINSAL\IndicadoresBundle\Repository;

use Doctrine\ORM\EntityRepository;

class BitacoraRepository extends EntityRepository
{
    /**
     * Devuelve los datos de la tabla de log
     */
    public function getLog() {
        $em = $this->getEntityManager();
        
        $sql = "SELECT B.username AS usuario, A.id_session AS id_sesion, 
                    A.accion,A.fecha_hora,  EXTRACT(MONTH FROM fecha_hora) AS mes, 
                    EXTRACT(YEAR FROM fecha_hora) AS anio, AA.duracion AS duracion_sesion_minutos
                FROM bitacora A 
                INNER JOIN fos_user_user B on (A.id_usuario = B.id)
                INNER JOIN 
                    ( SELECT id_session, ROUND( EXTRACT(epoch FROM MAx(fecha_hora)- MIN(fecha_hora)) / 60) AS duracion
                        FROM bitacora GROUP BY id_session
                    ) AA ON (A.id_session = AA.id_session)
                    ";
                
        return $em->getConnection()->executeQuery($sql)->fetchAll();
    }
}
