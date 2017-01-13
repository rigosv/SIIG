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
                    EXTRACT(YEAR FROM fecha_hora) AS anio, 
                    ROUND(EXTRACT(epoch FROM ((SELECT MAX(fecha_hora) - MIN(fecha_hora) 
                        FROM bitacora AA WHERE AA.id_session = A.id_session))) / 60)
                    AS duracion_sesion_minutos
                FROM bitacora A 
                INNER JOIN fos_user_user B on (A.id_usuario = B.id)";
                
        return $em->getConnection()->executeQuery($sql)->fetchAll();
    }
}
