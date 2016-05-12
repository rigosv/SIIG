<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use MINSAL\GridFormBundle\Entity\FormularioRepository;
use MINSAL\GridFormBundle\Entity\Formulario;

/**
 * IndicadorRepository
 * 
 */
class IndicadorRepository extends EntityRepository {

    public function getIndicadoresEvaluados($periodo) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        $datos = array();
        $calificaciones = array();
        
        //Recuperar los indicadores que no tienen asociado ningún criterio
        //Estos serán los que están cargados a todo el estándar
        $sql = "SELECT A.codigo, A.descripcion, A.forma_evaluacion,  A.porcentaje_aceptacion,
                    B.descripcion descripcion_estandar, B.periodo_lectura_datos, B.id as estandar_id,
                    (SELECT COUNT(codigo)
                        FROM variable_captura
                        WHERE formulario_id = B.id
                            AND es_separador = false
                            AND es_poblacion = false
                    ) AS total_criterios
                    FROM indicador A
                    INNER JOIN costos.formulario B ON (A.estandar_id = B.id)
                    WHERE A.id NOT IN (SELECT indicador_id FROM indicador_variablecaptura)
                    ORDER BY B.codigo, A.codigo
                    ";
        $indicadores = $em->getConnection()->executeQuery($sql)->fetchAll();
        
        foreach ($indicadores as $ind){
            
            $Frm = $em->getRepository('GridFormBundle:Formulario')->find($ind['estandar_id']);
            $eval_ = $this->getDatosEvaluacion($Frm, $periodo, $ind['total_criterios'], $ind['porcentaje_aceptacion'], $ind['forma_evaluacion'] );
            
            $calificacion = 0;
            $eval = array();
            foreach($eval_ as $e){
                $calificacion += $e['calificacion'];
            }
            $calificacionIndicador = (count($eval_) > 0) ? ($calificacion / count($eval_)) : 0;
            foreach($eval_ as $e){
                $f = $e;
                $f['calificacion_indicador'] = $calificacionIndicador;
                $eval[] = $f;
            }
            
            $datos[] = array('descripcion_estandar'=>$ind['descripcion_estandar'],  
                            'descripcion_indicador'=>$ind['descripcion'] ,
                            'codigo_indicador'=>$ind['codigo'] ,
                            'calificacion' => $calificacionIndicador,
                            'evaluacion' => $eval,
                            'category'=> $ind['codigo'] ,
                            'measure' => $calificacionIndicador
                        );
            $calificaciones[] = $calificacionIndicador;
            
        }
        $datos_original = $datos;
        array_multisort($calificaciones, SORT_DESC, $datos);
        $limite = (count($datos) > 10) ? 10 : count($datos);
        $datosT10 = $datos;
        $datosL10 = $datos;
        for($i = 0; $i < $limite; $i++){
           $less10[] = array_pop($datosL10);
           $top10[] = array_shift($datosT10);
        }
        $resp[] = array('datos'=>$datos_original, 'top10'=>$top10, 'less10'=>$less10);
        return $resp;
    }
    
    protected function getDatosEvaluacion(Formulario $Frm, $periodo, $totalCriterios, $porcentajeAprobacion, $formaEvaluacion){
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $campos = $em->getRepository('GridFormBundle:Formulario')->getListaCampos($Frm);
        $periodo_lectura = '';
        if ($Frm->getPeriodoLecturaDatos() == 'mensual' and $mes != null){
            $periodo_lectura = " AND (A.datos->'mes')::integer = '$mes' ";
        }
        
        $frmId = $Frm->getId();
        //Sacar los datos sobre los que se harán los cálculos
        $datos = "SELECT $campos, A.datos->'establecimiento' as establecimiento
                    FROM almacen_datos.repositorio A                         
                     WHERE id_formulario = '$frmId'
                        AND A.datos->'es_poblacion' = 'false'
                        AND A.datos->'es_separador' = 'false'
                        AND A.datos->'anio' = '$anio'
                        $periodo_lectura";
        if ($formaEvaluacion == 'cumplimiento_porcentaje_aceptacion'){
            $evaluacion = " (COALESCE(B.total_cumplimiento, 0) / A.total_expedientes * 100)  AS calificacion ";
        }elseif ($formaEvaluacion == 'cumplimiento_criterios') {
            $evaluacion = " (C.total_criterios_cumplidos / (A.total_expedientes * $totalCriterios) * 100) AS calificacion ";
        }
        $sql = "SELECT * FROM 
                (   SELECT A.establecimiento, A.total_expedientes, COALESCE(B.total_cumplimiento, 0) AS expedientes_cumple,
                    $totalCriterios AS criterios_evaluados, C.total_criterios_cumplidos,
                    $evaluacion                    
                    FROM 
                        (SELECT AA.establecimiento, COUNT(AA.nombre_pivote) AS total_expedientes
                            FROM (
                                SELECT establecimiento, nombre_pivote
                                FROM ($datos) AS D 
                                WHERE dato = 'true'
                                GROUP BY establecimiento, nombre_pivote
                            ) AS AA
                            GROUP BY AA.establecimiento
                        ) AS A
                    LEFT JOIN 
                        (   SELECT establecimiento, count(total_cumplimiento) AS total_cumplimiento
                            FROM (
                                SELECT establecimiento, COUNT(codigo_variable) AS total_cumplimiento
                                FROM ($datos) AS D
                                WHERE dato = 'true'
                                GROUP BY establecimiento, nombre_pivote
                                HAVING (COUNT(codigo_variable)/$totalCriterios * 100) >= $porcentajeAprobacion
                            ) AS BB
                            GROUP BY BB.establecimiento
                        ) AS B 
                        ON (A.establecimiento = B.establecimiento)
                    LEFT JOIN (
                        SELECT establecimiento, COUNT(codigo_variable) AS total_criterios_cumplidos
                            FROM ($datos) AS D
                            WHERE dato = 'true'
                            GROUP BY establecimiento
                    ) AS C ON (A.establecimiento = C.establecimiento)
                    INNER JOIN ctl_establecimiento_simmow ES ON (A.establecimiento = ES.id::text)                
                ) AS F
                ORDER BY calificacion DESC
         ";
        return $em->getConnection()->executeQuery($sql)->fetchAll();
    }
}