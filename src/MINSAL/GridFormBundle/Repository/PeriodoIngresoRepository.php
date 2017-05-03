<?php

namespace MINSAL\GridFormBundle\Repository;

use Doctrine\ORM\EntityRepository;
use \MINSAL\GridFormBundle\Entity\PeriodoIngreso;

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
    
    public function cerrarPeriodo($anio, $mes, $area){
        $em = $this->getEntityManager();
        
        //Borrar periodos diferentes al mes actual
        $sql = "DELETE
                FROM costos.periodo_ingreso_grupo_usuarios
                WHERE anio_periodo = $anio
                    AND mes_periodo NOT IN ('$mes', '0$mes')
                    AND formulario_id 
                        IN 
                        (SELECT id FROM costos.formulario WHERE area_costeo = '$area')
                ";
        
        $em->getConnection()->executeUpdate($sql);
        
        //Calcular el siguiente periodo
        if ($mes == 12){
            $anio_sig = $anio + 1;
            $mes_sig = '01';
        } else {
            $anio_sig = $anio;
            $mes_sig = str_pad($mes + 1, 2, "0", STR_PAD_LEFT);
        }
        
        //Verificar si existe el siguiente periodo, sino insertarlo
        $per_ = $em->getRepository("GridFormBundle:PeriodoIngreso")->findBy(array('anio'=> $anio_sig, 'mes'=>$mes_sig));
        
        if (count($per_) == 0){
            $periodo = new PeriodoIngreso();            
            $periodo->setAnio($anio_sig);
            $periodo->setMes($mes_sig);
            
            $em->persist($periodo);
            $em->flush();
        }
        
        //Actualizar todos los permisos del mes actual al siguiente mes
        $sql = "UPDATE costos.periodo_ingreso_grupo_usuarios
                    SET anio_periodo = $anio_sig, 
                        mes_periodo = '$mes_sig'
                WHERE anio_periodo = $anio
                    AND mes_periodo IN ('$mes', '0$mes')
                    AND formulario_id 
                        IN 
                        (SELECT id FROM costos.formulario WHERE area_costeo = '$area')
                    
                ";
        $em->getConnection()->executeUpdate($sql);
    }

}
