<?php

namespace MINSAL\CalidadBundle\Repository;

use Doctrine\ORM\EntityRepository;
use MINSAL\GridFormBundle\Entity\PeriodoIngreso;
use MINSAL\CostosBundle\Entity\Estructura;
use MINSAL\CalidadBundle\Entity\Estandar;

/**
 * EstandarRepository
 *
 */
class EstandarRepository extends EntityRepository {
    /**
     * 
     * @param text $establecimiento, código de estructura
     * @param text $periodo, anio_mes
     * @param type $formulario, codigo del formulario
     * @return type
     */
    public function getCriterios($establecimiento, $periodo, $formulario) {
        $em = $this->getEntityManager();
        return $em->getRepository('GridFormBundle:Formulario')->getCriterios($establecimiento, $periodo, $formulario);
    }
    
    public function getIndicadoresEvaluadosNumericos(Estructura $establecimiento, PeriodoIngreso $periodo, Estandar $estandar = null) {
        $em = $this->getEntityManager();
        $mes = $periodo->getMes();
        $anio = $periodo->getAnio();
        $codigo = $establecimiento->getCodigo();
        
        $whereFormulario = '';
        if ($estandar != null){
            $idFrm = $estandar->getFormularioCaptura()->getId();
            $whereFormulario = " WHERE FF.id = $idFrm OR FF.id_formulario_sup = $idFrm";
        }
        
        $sql = "SELECT establecimiento, anio, mes, id_formulario, area,  codigo_criterio as codigo_variable, calificacion, 
                        COALESCE(AA.metaVar, AA.metaInd) AS meta, AA.metaVAr, AA.metaInd, 
                        COALESCE(AA.colorVar, AA.colorInd) AS color, colorVar, colorInd,
                        historial, FF.codigo
                FROM (
                    SELECT COALESCE(B.id_formulario_sup, B.id) AS id_formulario, codigo_criterio, D.descripcion AS area, A.id_indicador,
                        A.anio, A.mes, A.calificacion, A.establecimiento,
                    (SELECT color 
                        FROM indicador_rangoalerta RA 
                            INNER JOIN rango_alerta BB ON (RA.rangoalerta_id = BB.id)
                        WHERE A.calificacion BETWEEN COALESCE(limite_inferior, -100000) AND COALESCE(limite_superior, 1000000)
                            AND indicador_id = E.id
                        ) AS colorInd,
                    (SELECT color 
                        FROM variablecaptura_rangoalerta VRA 
                            INNER JOIN rango_alerta RA ON (VRA.rangoalerta_id = RA.id)
                        WHERE A.calificacion BETWEEN COALESCE(limite_inferior, -100000) AND COALESCE(limite_superior, 1000000)
                            AND variablecaptura_id = C.id
                        ) AS colorVar,
                    (SELECT COALESCE(limite_inferior, -100000) ||'-'|| COALESCE(limite_superior, 1000000)
                        FROM indicador_rangoalerta RA 
                            INNER JOIN rango_alerta BB ON (RA.rangoalerta_id = BB.id)
                        WHERE color='green'
                            AND indicador_id = E.id
                        ) AS metaInd,
                    (SELECT COALESCE(limite_inferior, -100000) ||'-'|| COALESCE(limite_superior, 1000000)
                        FROM variablecaptura_rangoalerta VRA 
                            INNER JOIN rango_alerta RA ON (VRA.rangoalerta_id = RA.id)
                        WHERE color='green'
                            AND variablecaptura_id = C.id
                        ) AS metaVar,
                    array(
                        SELECT mes||'/'||anio||'/'||ROUND(AVG(calificacion)::numeric,2)
                            FROM datos_evaluacion_calidad_num AA
                            WHERE AA.id_indicador = A.id_indicador
                                AND calificacion != 'NaN'
                                AND (anio < $anio OR (anio = $anio AND mes::integer <= $mes::integer ) )
                                AND AA.establecimiento = '$codigo'
                                AND AA.codigo_criterio = A.codigo_criterio
                            GROUP BY anio, mes
                            ORDER BY anio DESC, mes DESC
                            LIMIT 10
                        ) AS historial
                    FROM datos_evaluacion_calidad_num A
                        INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                        INNER JOIN variable_captura C ON (A.codigo_criterio = C.codigo)
                        INNER JOIN area_variable_captura D ON (A.id_area_criterio = D.id)
                        INNER JOIN indicador E ON (A.id_indicador = E.id)
                        
                    WHERE A.mes::integer = $mes::integer
                        AND A.anio = '$anio'
                        AND A.establecimiento = '$codigo'
                    ) AS AA
                    INNER JOIN costos.formulario FF ON (AA.id_formulario = FF.id)
                    $whereFormulario
                    ORDER BY establecimiento, id_formulario, area;
                    ";
        $datos = $em->getConnection()->executeQuery($sql)->fetchAll();   
        return $datos;
    }
    
}
