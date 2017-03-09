<?php

namespace MINSAL\GridFormBundle\Repository;

use Doctrine\ORM\EntityRepository;

class PeriodoIngresoRepository extends EntityRepository {

    public function getPeriodosEvaluadosCalidad() {
        $sql = "SELECT anio, mes
                FROM datos_evaluacion_calidad
                GROUP BY anio, mes";
        
        $consPer = $this->getEntityManager()->getConnection()->executeQuery($sql)->fetchAll();
        
        $periodos = array();
        
        foreach ($consPer as $e){
            $periodos[] = $e['anio'].$e['mes'];
            $periodos[] = $e['anio'].'0'.$e['mes'];
        }
        
        $qb = $this->createQueryBuilder('p')
                ->where(" CONCAT (p.anio, p.mes) IN ('".implode("', '", $periodos)."')")
                ->orderBy('p.anio', 'DESC')
                ->addOrderBy('p.mes', 'DESC')
                ;
        return $qb;
        
    }

}
