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
    
    public function getDatosCalidad($idFormulario) {
        $em = $this->getEntityManager();
        
        if ($idFormulario == 'general_pna' or $idFormulario == 'general_hosp'){
            $nivel = explode('_', $idFormulario);
            $niveles = $em->getRepository('GridFormBundle:Indicador')->getNivelesEstablecimiento($nivel[1]);
            
            $sql = "SELECT codigo_estandar, B.nombre AS estandar, codigo_indicador, anio, mes, 
                        D.descripcion AS sibasi, E.descripcion AS region,
                        descripcion_indicador, calificacion AS calificacion_indicador, 
                        nombre_establecimiento, nombre_corto AS establecimiento_nombre_corto,
                        ROUND(COALESCE(( SELECT COUNT(calificacion)
                            FROM  datos_evaluacion_calidad AA
                            WHERE AA.codigo_estandar = A.codigo_estandar
                                AND AA.anio = A.anio
                                AND AA.mes = A.mes
                                AND AA.establecimiento = A.establecimiento
                                AND AA.calificacion >= 80
                            GROUP BY codigo_estandar, anio, mes, establecimiento
                        ), 0)::numeric / 
                        ( SELECT COUNT(calificacion)
                            FROM  datos_evaluacion_calidad AA
                            WHERE AA.codigo_estandar = A.codigo_estandar
                                AND AA.anio = A.anio
                                AND AA.mes = A.mes
                                AND AA.establecimiento = A.establecimiento
                            GROUP BY codigo_estandar, anio, mes, establecimiento
                        )::numeric * 100,2) AS calificacion_estandar
                    FROM datos_evaluacion_calidad A
                        INNER JOIN costos.formulario B ON (A.codigo_estandar = B.codigo)
                        INNER JOIN ctl_establecimiento_simmow C ON (A.establecimiento = C.id::varchar)
                        INNER JOIN ctl_sibasi D ON (C.idsibasi = D.id)
                        INNER JOIN ctl_regiones_simmow E ON (C.idregion = E.id)
                    WHERE C.id_tipo_establecimiento IN $niveles
                        ";
            return $em->getConnection()->executeQuery($sql)->fetchAll();
        }
        
        $frm = $em->getRepository("GridFormBundle:Formulario")->find($idFormulario);
        
        if ($frm->getPeriodoLecturaDatos() == 'mensual' ){
            $per = " mes";
        } else {
            $per = " nombre_pivote ";
        }
        $whereFrm6 = '';
        if ($idFormulario == 104){
            $whereFrm6 = " AND tipo_control = 'checkbox' ";
        }
        $em->getRepository("GridFormBundle:Formulario")->getDatosEvaluacion($frm);
        
        $sql = "SELECT anio, mes, COALESCE(B.nombre_corto, B.nombre) AS establecimiento, C.nombre AS estandar,
                    H.descripcion AS sibasi, I.descripcion AS region,
                    F.codigo as codigo_indicador, F.descripcion AS nombre_indicador, descripcion_variable AS criterio, 
                    SUM(cumplimiento) as cumplimiento, 
                    SUM(no_cumplimiento) AS no_cumplimiento, 
                    ROUND((SUM(cumplimiento)::numeric / ( SUM(cumplimiento)::numeric + SUM(no_cumplimiento)::numeric ) * 100),0) AS porc_cumplimiento                    
                FROM (
                    SELECT anio, $per as mes, establecimiento, id_formulario as formulario, codigo_variable, descripcion_variable, COALESCE(NULLIF(posicion, ''), '0')::numeric AS posicion, id_formulario,
                        CASE WHEN dato = 'true' OR dato = '1' THEN 1 ELSE 0 END AS cumplimiento, 
                        CASE WHEN tipo_control = 'checkbox' AND dato != 'true' and dato != '1' THEN 1 
                            WHEN tipo_control = 'checkbox_3_states' AND dato = 'false' or dato = '0' THEN 1
                            ELSE 0 
                        END AS no_cumplimiento
                        FROM datos_tmp 
                        WHERE es_poblacion='false'
                            AND es_separador != 'true'
                            AND tipo_control != 'dropdownlist'
                            $whereFrm6
                            
                    ) AS A 
                    INNER JOIN costos.estructura B ON (A.establecimiento = B.codigo)
                    INNER JOIN costos.formulario C ON (A.formulario = C.id)
                    INNER JOIN variable_captura D ON (A.codigo_variable = D.codigo)
                    INNER JOIN ctl_establecimiento_simmow G ON (A.establecimiento = G.id::varchar)
                    INNER JOIN ctl_sibasi H ON (G.idsibasi = H.id)
                    INNER JOIN ctl_regiones_simmow I ON (G.idregion = I.id)
                    LEFT JOIN indicador_variablecaptura E ON (D.id = E.variablecaptura_id)
                    LEFT JOIN indicador F ON (E.indicador_id = F.id)
                GROUP BY anio, mes, I.descripcion, H.descripcion, B.nombre_corto, B.nombre, establecimiento, A.formulario, C.nombre, F.codigo, F.descripcion, codigo_variable, descripcion_variable, A.posicion 
                HAVING (SUM(cumplimiento)::numeric + SUM(no_cumplimiento)::numeric) > 0 
                ORDER BY A.formulario, A.posicion::numeric";
        
        return $em->getConnection()->executeQuery($sql)->fetchAll();
          
    }
}
