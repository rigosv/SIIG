<?php

namespace MINSAL\CostosBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MINSAL\GridFormBundle\Entity\PeriodoIngreso;
use MINSAL\CostosBundle\Entity\Estructura;

/**
 * EstructuraRepository
 * 
 */
class EstructuraRepository extends EntityRepository {

    public function getEstablecimientosEvaluadosCalidad(PeriodoIngreso $p) {
        $mes = $p->getMes();
        $anio = $p->getAnio();
        $sql = "SELECT DISTINCT establecimiento
                FROM datos_evaluacion_calidad
                WHERE anio = '$anio' 
                    AND (mes = '$mes' OR '0'||mes = '$mes') ";
        
        $consEst = $this->getEntityManager()->getConnection()->executeQuery($sql)->fetchAll();
        
        $establecimientos = array();
        
        foreach ($consEst as $e){
            $establecimientos[] = $e['establecimiento'];
        }
        
        $qb = $this->createQueryBuilder('e')
                ->where("e.codigo IN ('".implode("', '", $establecimientos)."')")
                ->orderBy('e.nombreCorto')
                ;
        return $qb;
    }
    
    public function getEstablecimiento(Estructura $estructura) {
        $codigo = $estructura->getCodigo();
        
        $sql = "SELECT A.descripcion nombre, B.descripcion as region
                FROM ctl_establecimiento_simmow A
                    INNER JOIN ctl_regiones_simmow B ON (A.idregion = B.id)
                WHERE A.id = '$codigo' ";

        $est = $this->getEntityManager()->getConnection()->executeQuery($sql)->fetch();

        return $est;
    }
}