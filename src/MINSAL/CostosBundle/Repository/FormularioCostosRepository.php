<?php

namespace MINSAL\CostosBundle\Repository;

use MINSAL\GridFormBundle\Repository\FormularioRepository AS FormularioRepositoryBase;
use MINSAL\GridFormBundle\Entity\Formulario;
use Symfony\Component\HttpFoundation\Request;

/**
 * FormularioRepository
 * 
 */
class FormularioCostosRepository extends FormularioRepositoryBase {

    public function getDatosCostosGA(Formulario $Frm,  Request $request) {
        $em = $this->getEntityManager();
        $area = $Frm->getAreaCosteo();

        list($mes, $anio) = explode('/',$request->get('anio_mes'));
        $this->parametros = array('anio'=>$anio, 'mes'=>$mes, 'establecimiento'=>$request->get('establecimiento'));
        
        $orden = '';
        
        $params_string = "AND datos->'mes' = '$mes' AND datos->'anio' = '$anio' AND datos->'establecimiento' = '".$this->parametros['establecimiento']."' ";
        
        if ($area == 'ga_costos' ){
            $area = 'ga';
            $origenes = array($Frm->getId());
            $campo = 'id_formulario';
            
            
            //Recuperar las variables de los gastos administrativos
            $FrmVar = $em->getRepository('GridFormBundle:Formulario')->findOneBy(array('codigo'=>'gaVariables'));
            // Agregar acá las variables de los elevadores, porque daría error si ninguna dependencia tiene alguno de estos elevadores
            // Así se pondrán en null y no dará error por no existir los campos
            $campos_gaVar = "datos->'elevadores_personas' AS elevadores_personas, datos->'elevadores_verdes' AS elevadores_verdes,
                                datos->'elevadores_quemados' AS elevadores_quemados, ";
            $campos_gaVar = "";
            $campos_gaVar2 = " B.elevadores_personas, B.elevadores_verdes, B.elevadores_quemados, ";
            $campos_gaVar2 = "";
            foreach($FrmVar->getCampos() as $c){
                if ($c->getOrigenPivote() == ''){
                    $codigo = $c->getSignificadoCampo()->getCodigo();
                    //if (!in_array($codigo, array('elevadores_personas', 'elevadores_verdes', 'elevadores_quemados')))
                        $campos_gaVar .= "datos->'".$codigo. "' AS ". $codigo.', ';
                    
                    if (!in_array($codigo, array('dependencia')))
                        $campos_gaVar2 .= 'B.'.$codigo.', ';
                }
            }
            $campos_gaVar = trim ($campos_gaVar, ', ');
            $campos_gaVar2 = trim ($campos_gaVar2, ', ');
            
            
            $campos = "datos->'establecimiento' AS establecimiento, datos->'anio' AS anio, 
                        datos->'mes' AS mes, datos->'dependencia' AS dependencia, ";
            
            $pivotes = array();
            
            foreach($Frm->getCampos() as $c){
                if ($c->getOrigenPivote()){
                    $pivotes[$c->getSignificadoCampo()->getCodigo()] = $em->getRepository('GridFormBundle:Campo')->getOrigenPivote($c, $this->parametros);
                }
            }
            
            $otros_campos = '';            
            $piv1 = '';
            $piv2 = '';
            
            foreach ($pivotes as $nombre_pivote=>$p){
                foreach ($p as $piv){
                    $cod = array_shift($piv);
                    $otros_campos .= "datos->'".$nombre_pivote."_". $cod."' AS ".'"'.strtolower($cod).'"'.", ";
                    $piv1 .= "'".$cod."', ";
                    $piv2 .= strtolower($cod).", ";
                }
            }

            $piv1 = trim($piv1, ', ');
            $piv2 = trim($piv2, ', ');
            $otros_campos = trim($otros_campos, ', ');
            
            $campos .=  $otros_campos;
            $campos = trim($campos, ', ');
            
            
            /**************************************************************
             * Agregar todos los gastos que se han puesto en el formulario 
             * de distribución de compromisos
            ****************************************************************/
            $sql = "SELECT B.*, B1.codigo AS criterio_distribucion,  D.codigo as variable_calculo_consumo, A1.ubicacion_id "
                    . "INTO TEMP ini_gastos_administrativos_tmp "
                    . 'FROM (SELECT establecimiento, dependencia, anio, mes, unnest(array['.$piv1.']) AS codigo_compromiso, '
                                    . 'unnest(array['.$piv2.']) AS consumo_dependencia 
                                FROM 
                                    ( '.
                                        "SELECT $campos
                                            FROM costos.fila_origen_dato_".strtolower($area)." 
                                            WHERE $campo IN (" . implode(',', $origenes) . ")
                                                $params_string
                                    ) as A
                            ) AS B 
                                INNER JOIN costos.contratos_fijos_ga A1 ON (B.codigo_compromiso = A1.codigo)
                                LEFT JOIN costos.campo C ON (A1.variablecalculoconsumo_id = C.id)
                                LEFT JOIN significado_campo D ON (C.significadocampo_id = D.id)
                                INNER JOIN costos.criterios_distribucion_ga B1 ON (A1.criteriodistribucion_id = B1.id)
                    ";
            $em->getConnection()->executeQuery($sql);   
            
            /**************************************************************
             * Agregar otros compromisos que no están en el formulario 
             * de distribución de compromisos
             * y no fueron agregadas por la consulta sql anterior
            *****************************************************************/
            $sql = "INSERT INTO ini_gastos_administrativos_tmp 
                        SELECT  D.establecimiento, D.dependencia, 
                            '".$this->parametros['anio']."' AS anio , 
                            '".$this->parametros['mes']."' AS mes, 
                            A.codigo AS codigo_compromiso, '0' AS consumo_dependencia,
                            E.codigo AS criterio_distribucion , DD.codigo as variable_calculo_consumo,
                            A.ubicacion_id
                        FROM costos.contratos_fijos_ga A 
                            INNER JOIN estructura_contratosfijosga B ON (A.id = B.contratosfijosga_id)
                            LEFT JOIN costos.campo CC ON (A.variablecalculoconsumo_id = CC.id)
                            LEFT JOIN significado_campo DD ON (CC.significadocampo_id = DD.id)
                            INNER JOIN costos.estructura C ON (B.estructura_id = C.id) 
                            INNER JOIN (
                                SELECT A.codigo AS dependencia, C.codigo AS establecimiento 
                                    FROM costos.estructura A  
                                    INNER JOIN costos.estructura B ON (A.parent_id = B.id) 
                                    INNER JOIN costos.estructura C ON (B.parent_id = C.id) 
                            ) AS D ON (C.codigo = D.establecimiento) 
                            INNER JOIN costos.criterios_distribucion_ga E ON (A.criteriodistribucion_id = E.id) 
                        WHERE (D.establecimiento, D.dependencia, '".$this->parametros['anio']."' , '".$this->parametros['mes']."' , A.codigo)
                            NOT IN (SELECT establecimiento, dependencia, anio, mes, codigo_compromiso FROM ini_gastos_administrativos_tmp)
                     ";
            $em->getConnection()->executeQuery($sql);

            /**************************************************************
             * El monto de cada compromiso mensual del establecimiento
            *****************************************************************/
            $sqlMontoCompromiso = "
                ( SELECT SUM((COALESCE(NULLIF(importe, ''),'0'))::numeric)
                    FROM 
                        -- **************************************************************** 
                        -- * Compromisos financieros por cada mes
                        -- *****************************************************************
                        (SELECT establecimiento, anio, codigo_contrato, 
                            unnest(array['m01', 'm02', 'm03', 'm04', 'm05', 'm06', 'm07', 'm08', 'm08', 'm10', 'm11', 'm12']) as mes,
                            unnest(array[m01, m02, m03, m04, m05, m06, m07, m08, m08, m10, m11, m12]) as importe                                        
                            FROM 
                                (SELECT datos->'establecimiento' AS establecimiento, datos->'anio' AS anio, 
                                            datos->'codigo_contrato' AS codigo_contrato, 
                                            datos->'importe_mensual_01' as ".'"m01"'. ", datos->'importe_mensual_02' as ".'"m02",'.
                                            "datos->'importe_mensual_03' as ".'"m03"'. " , datos->'importe_mensual_04' as ". '"m04", '.
                                            "datos->'importe_mensual_05' as ".'"m05"'. " , datos->'importe_mensual_06' as ". '"m06", '.
                                            "datos->'importe_mensual_07' as ".'"m07"'. " , datos->'importe_mensual_08' as ". '"m08", '.
                                            "datos->'importe_mensual_09' as ".'"m09"'. " , datos->'importe_mensual_11' as ". '"m10", '.
                                            "datos->'importe_mensual_11' as ".'"m11"'. " , datos->'importe_mensual_12' as ". '"m12" '.
                                        "FROM costos.fila_origen_dato_ga 
                                        WHERE datos->'establecimiento' = A.establecimiento
                                            AND datos->'anio' = A.anio
                                            AND datos->'codigo_contrato' = A.codigo_compromiso
                                ) AS SS
                        ) AS AA
                    WHERE establecimiento = A.establecimiento
                        AND anio = A.anio
                        AND codigo_contrato = A.codigo_compromiso
                        AND mes = 'm'||A.mes
                ) AS compromiso
                     ";
            
            /**************************************************************
             * Información que se obtiene del formulario de activo fijo
             * (datos por dependecia, mes, anio)
             * consumo_watt, carga_con_planta_emerg
            *****************************************************************/
            $sqlActivoFijo = " 
                        (SELECT SUM((COALESCE(NULLIF(datos->'consumo_kw_mes', ''),'0'))::numeric)
                            FROM costos.fila_origen_dato_ga_af
                            WHERE datos->'establecimiento' = A.establecimiento
                                AND datos->'dependencia' = A.dependencia
                                AND datos->'anio' = A.anio
                                AND datos->'mes' = A.mes
                            
                        ) AS consumo_kw_dependecia,
                        (SELECT SUM((COALESCE(NULLIF(datos->'consumo_kw_mes', ''),'0'))::numeric)
                            FROM costos.fila_origen_dato_ga_af
                            WHERE datos->'establecimiento' = A.establecimiento
                                AND datos->'dependencia' = A.dependencia
                                AND datos->'anio' = A.anio
                                AND datos->'mes' = A.mes
                                AND datos->'usa_planta_emergencia' = 'true'
                        ) AS carga_con_planta_emerg_dependecia,
                        (SELECT SUM((COALESCE(NULLIF(datos->'depreciacion', ''),'0'))::numeric)
                            FROM costos.fila_origen_dato_ga_af
                            WHERE datos->'establecimiento' = A.establecimiento
                                AND datos->'dependencia' = A.dependencia
                                AND datos->'anio' = A.anio
                                AND datos->'mes' = A.mes
                                AND datos->'tipo_activo' = 'EM'
                        ) AS depreciacion_equip_medic_y_mobiliario,
                        (SELECT SUM((COALESCE(NULLIF(datos->'depreciacion', ''),'0'))::numeric)
                            FROM costos.fila_origen_dato_ga_af
                            WHERE datos->'establecimiento' = A.establecimiento
                                AND datos->'dependencia' = A.dependencia
                                AND datos->'anio' = A.anio
                                AND datos->'mes' = A.mes
                                AND datos->'tipo_activo' = 'EIT'
                        ) AS depreciacion_equip_infor_y_transporte
                      ";
            
            /**************************************************************
             * Agregar todas las variables del establecimiento 
             * (Las que se capturan en el formulario gaVariables)
            *****************************************************************/
            $sql = " SELECT A.*, $campos_gaVar2, $sqlMontoCompromiso, $sqlActivoFijo
                        INTO  TEMP gastos_administrativos_tmp
                        FROM ini_gastos_administrativos_tmp A
                            LEFT JOIN (SELECT datos->'establecimiento' AS establecimiento, datos->'anio' AS anio, datos->'mes' as mes,
                                            $campos_gaVar                                
                                            FROM costos.fila_origen_dato_ga
                                            WHERE area_costeo = 'ga_variables'
                                        ) AS B ON (A.establecimiento = B.establecimiento AND A.anio = B.anio 
                                            AND A.mes = B.mes AND A.dependencia = B.dependencia)
                     ";

            $em->getConnection()->executeQuery($sql);
            

            /**************************************************************
             * Se agregan los cálculos para ciertos compromisos
            *****************************************************************/
            $sql = "
                    SELECT *,
                            CASE 
                                -- **************************************************************** 
                                -- * DEL CONSUMO DEL AGUA
                                -- *****************************************************************
                                WHEN (codigo_compromiso = 'h2o' AND (COALESCE(NULLIF(tot_personal, ''),'0'))::numeric = 0 
                                            AND (COALESCE(NULLIF(prom_usuarios_dia, ''),'0'))::numeric = 0) 
                                    THEN 0
                                WHEN (codigo_compromiso = 'h2o' AND (tipo_centro = '5' OR tipo_centro = '6')) 
                                    THEN ((COALESCE(NULLIF(cant_camas, ''),'0'))::numeric * 0.18) + 
                                        ((COALESCE(NULLIF(tot_personal, ''),'0'))::numeric *0.06) + 
                                        ((COALESCE(NULLIF(prom_usuarios_dia, ''),'0'))::numeric * 0.03) + 
                                        ((COALESCE(NULLIF(area_tot, ''),'0'))::numeric * 0.006)
                                WHEN (codigo_compromiso = 'h2o' AND tipo_centro <> '5' AND tipo_centro <> '6') 
                                    THEN ((COALESCE(NULLIF(tot_personal, ''),'0'))::numeric * 0.06) + 
                                        ((COALESCE(NULLIF(prom_usuarios_dia, ''),'0'))::numeric * 0.03) + 
                                        ((COALESCE(NULLIF(area_tot, ''),'0'))::numeric * 0.006 / 3)
                                
                                -- **************************************************************** 
                                -- * Energía electrica y mantenimiento de subestacion electrica
                                -- *****************************************************************
                                WHEN (codigo_compromiso = 'energia_electrica' OR codigo_compromiso = 'subestacion_electrica') 
                                    THEN (A.consumo_kw_dependecia)::numeric
                                                                
                                    
                                -- **************************************************************** 
                                -- * Mantenimiento de planta electrica y combustible
                                -- *****************************************************************
                                WHEN (codigo_compromiso = 'planta_emergencia' OR codigo_compromiso = 'combustible_planta_emergencia') 
                                    THEN (A.carga_con_planta_emerg_dependecia)::numeric
                                                                
                                    
                                -- **************************************************************** 
                                -- * Depreciacion contable de mobiliario
                                -- *****************************************************************
                                WHEN (codigo_compromiso = 'depreciacion_contable_mobiliario') 
                                    THEN (A.depreciacion_equip_medic_y_mobiliario)::numeric
                                
                                -- **************************************************************** 
                                -- * Depreciacion contable equipo informatico y transporte
                                -- *****************************************************************
                                WHEN (codigo_compromiso = 'depreciacion_contable_equipo') 
                                    THEN (A.depreciacion_equip_infor_y_transporte)::numeric
                        
                                -- **************************************************************** 
                                -- * Cuando es criterio de distribución es dependencia (ubicación) 
                                -- * como los elevadores 
                                -- *****************************************************************
                                WHEN (criterio_distribucion = 'dependencia' AND A.variable_calculo_consumo IS NOT NULL
                                            AND (SELECT COUNT(datos->A.variable_calculo_consumo)  
                                                                FROM costos.fila_origen_dato_ga 
                                                                WHERE area_costeo = 'ga_variables' 
                                                                    AND datos->'establecimiento' = A.establecimiento
                                                                    AND datos->'mes' = A.mes 
                                                                    AND datos->'anio' = A.anio 
                                                                    AND datos->A.codigo_compromiso = 'true'
                                                            )::numeric > 0
                                        ) THEN
                                            (SELECT SUM((COALESCE(NULLIF(datos->A.variable_calculo_consumo, ''),'0'))::numeric)  
                                                FROM costos.fila_origen_dato_ga 
                                                WHERE area_costeo = 'ga_variables' 
                                                    AND datos->'establecimiento' = A.establecimiento 
                                                    AND datos->'dependencia' = A.dependencia
                                                    AND datos->'mes' = A.mes 
                                                    AND datos->'anio' = A.anio 
                                                    AND datos->A.codigo_compromiso = 'true'
                                            )::numeric
                                            / 
                                            (SELECT SUM((COALESCE(NULLIF(datos->A.variable_calculo_consumo, ''),'0'))::numeric)  
                                                FROM costos.fila_origen_dato_ga 
                                                WHERE area_costeo = 'ga_variables' 
                                                    AND datos->'establecimiento' = A.establecimiento 
                                                    AND datos->'mes' = A.mes 
                                                    AND datos->'anio' = A.anio 
                                                    AND datos->A.codigo_compromiso = 'true'
                                            )::numeric
                                
                                -- ***************************************************************** 
                                -- * compromisos cuyo consumo se calcula a partir de alguna variable
                                -- * por ejemplo el gasto de Radios se hacen en base a la cantidad
                                -- * de radios que posee la dependencia
                                -- *****************************************************************
                                WHEN (A.variable_calculo_consumo IS NOT NULL
                                            AND (SELECT SUM(( (COALESCE(NULLIF(datos->A.variable_calculo_consumo, ''),'0')))::numeric)  
                                                    FROM costos.fila_origen_dato_ga 
                                                    WHERE area_costeo = 'ga_variables' 
                                                        AND datos->'establecimiento' = A.establecimiento 
                                                        AND datos->'mes' = A.mes 
                                                        AND datos->'anio' = A.anio
                                                )::numeric > 0
                                        ) THEN
                                            (SELECT SUM(( (COALESCE(NULLIF(datos->A.variable_calculo_consumo, ''),'0')))::numeric)  
                                                FROM costos.fila_origen_dato_ga 
                                                WHERE area_costeo = 'ga_variables' 
                                                    AND datos->'establecimiento' = A.establecimiento 
                                                    AND datos->'dependencia' = A.dependencia
                                                    AND datos->'mes' = A.mes 
                                                    AND datos->'anio' = A.anio 
                                            )::numeric
                                            / 
                                            (SELECT SUM(( (COALESCE(NULLIF(datos->A.variable_calculo_consumo, ''),'0')))::numeric)  
                                                FROM costos.fila_origen_dato_ga 
                                                WHERE area_costeo = 'ga_variables' 
                                                    AND datos->'establecimiento' = A.establecimiento 
                                                    AND datos->'mes' = A.mes 
                                                    AND datos->'anio' = A.anio
                                            )::numeric
                                ";
                      $sql .= " 
                                ELSE
                                    consumo_dependencia::numeric
                            END AS consumo_dependencia_final
                        INTO TEMP ga_tmp 
                        FROM 
                            (SELECT A.*,
                                -- El area de todo el establecimiento                                
                                (SELECT SUM(area_tot::numeric)
                                    FROM (SELECT (COALESCE(NULLIF(datos->'area_tot', ''),'0'))::numeric AS area_tot
                                        FROM costos.fila_origen_dato_ga
                                        WHERE area_costeo = 'ga_variables'
                                            AND datos->'establecimiento' = A.establecimiento
                                            AND datos->'anio' = A.anio
                                            AND datos->'mes' = A.mes
                                        ) AS CC
                                )  AS area_tot_establecimiento
                                
                            FROM gastos_administrativos_tmp A
                    ) AS A                                        
                    ";
            $em->getConnection()->executeQuery($sql);
            $em->getConnection()->executeQuery('UPDATE ga_tmp SET consumo_dependencia = consumo_dependencia_final ');

            /**********************************************************
             * Se hace la distribución del compromiso financiero 
             * del establecimiento 
             * de acuerdo el consumo de cada dependencia
             **********************************************************/
            $sql = "SELECT *, CASE 
                                WHEN (codigo_compromiso = 'depreciacion_contable_mobiliario' OR codigo_compromiso = 'depreciacion_contable_equipo')
                                    THEN consumo_dependencia::numeric
                                WHEN (consumo_establecimiento::numeric >0 AND (criterio_distribucion = 'consumo' OR criterio_distribucion = 'asignacion_directa' 
                                    OR criterio_distribucion = 'personas' OR criterio_distribucion = 'dependencia')) 
                                    THEN compromiso::numeric * consumo_dependencia::numeric / consumo_establecimiento::numeric
                                WHEN (ubicacion_id IS NOT NULL)
                                    THEN 
                                        compromiso::numeric * (SELECT SUM((datos->A.variable_calculo_consumo)::numeric)  
                                            FROM costos.fila_origen_dato_ga 
                                            WHERE area_costeo = 'ga_variables' 
                                                AND datos->'establecimiento' = A.establecimiento 
                                                AND datos->'dependencia' = A.dependencia
                                                AND datos->'mes' = A.mes 
                                                AND datos->'anio' = A.anio 
                                                AND datos->'dependencia' 
                                                    IN 
                                                    (SELECT codigo
                                                        FROM costos.estructura
                                                        WHERE ubicaciondependencia_id = A.ubicacion_id
                                                    )
                                        )::numeric
                                        / 
                                        (SELECT SUM((datos->A.variable_calculo_consumo)::numeric)  
                                            FROM costos.fila_origen_dato_ga 
                                            WHERE area_costeo = 'ga_variables' 
                                                AND datos->'establecimiento' = A.establecimiento
                                                AND datos->'mes' = A.mes 
                                                AND datos->'anio' = A.anio
                                                AND datos->'dependencia' 
                                                    IN 
                                                    (SELECT codigo
                                                        FROM costos.estructura
                                                        WHERE ubicaciondependencia_id = A.ubicacion_id
                                                    )
                                        )::numeric
                                WHEN (criterio_distribucion = 'area_mt2' AND area_tot_establecimiento::numeric > 0) THEN compromiso::numeric * area_tot::numeric / area_tot_establecimiento::numeric                              
                            END AS total_gasto
                    FROM (SELECT A.*,
                                B.nombre AS nombre_dependencia, C.descripcion AS nombre_criterio_distribucion, D.descripcion AS nombre_compromiso,
                                (SELECT SUM(consumo_dependencia::numeric) 
                                    FROM ga_tmp 
                                    WHERE establecimiento = A.establecimiento
                                        AND anio = A.anio
                                        AND mes = A.mes
                                        AND codigo_compromiso = A.codigo_compromiso
                                ) AS consumo_establecimiento
                            FROM ga_tmp A
                               INNER JOIN costos.estructura B ON (A.dependencia = B.codigo)
                               INNER JOIN costos.criterios_distribucion_ga C ON (A.criterio_distribucion = C.codigo)
                               INNER JOIN costos.contratos_fijos_ga D ON (A.codigo_compromiso = D.codigo)
                        ) AS A
                    ORDER BY A.establecimiento, A.dependencia, A.anio, A.mes, A.criterio_distribucion, A.codigo_compromiso";
            
        }
        try {
            return $em->getConnection()->executeQuery($sql)->fetchAll();
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }
    public function getDatosCosteo($codigo) {
        $em = $this->getEntityManager();
        $sql1 = '';
        $sql2 = '';
        if ($codigo == 'rrhh') {
            $codigo_fuente_costos = 'rrhh';
            $otros_campos = " datos->'isss_patronal' AS ". '"Aporte Patronal ISSS", '.
                    " datos->'fondo_proteccion_patronal' AS ". '"Aporte Patronal Fondo Protección", '.
                    " datos->'costo_con_aporte_y_aguinaldo' AS ". '"Costo con aporte y aguinaldo", '.
                    " datos->'costo_hora_aporte_aguinaldo' AS ". '"Costo Hora con aporte y aguinaldo", '.
                    " datos->'costo_hora_no_trab_CG' AS ". '"Costo hora no trabajada con goce", '.
                    " datos->'costo_hora_no_trab_SG' AS ". '"Costo hora no trabajada SIN goce", '.
                    " datos->'salario_descuentos_permisos' AS ". '"Salario con descuentos y permisos", '.
                    " datos->'costo_hora_descuentos_permisos' AS ". '"Costo Hora con descuentos y permisos", '.
                    " datos->'dependencia' AS ". '"codigo_dependencia" '
                    ;
            
            $Frm = array_shift($em->getRepository('GridFormBundle:Formulario')->findBy(array('codigo' => 'rrhhValorPagado')));
        
            $campos = '';
            foreach ($Frm->getCampos() as $c){
                if ($c->getOrigenPivote() == ''){
                    $campos .= "datos->'".$c->getSignificadoCampo()->getCodigo(). "' AS ". '"'. $c->getSignificadoCampo()->getDescripcion().'", ';
                }
            }
        } elseif ($codigo == 'rrhh_resumen') {
            $codigo_fuente_costos = 'rrhh';
            
            $Frm = array_shift($em->getRepository('GridFormBundle:Formulario')->findBy(array('codigo' => 'rrhhDistribucionHora')));
            $Frm2 = array_shift($em->getRepository('GridFormBundle:Formulario')->findBy(array('codigo' => 'rrhhValorPagado')));
            
            $campos = '';
            $campos2 = '';
            foreach($Frm2->getCampos() as $c){
                if ($c->getOrigenPivote() == '' and ($c->getTipoDato()->getCodigo() == 'string' or $c->getTipoDato()->getCodigo() == 'bool') ){
                    $campos .= "datos->'".$c->getSignificadoCampo()->getCodigo(). "' AS ". '"'. $c->getSignificadoCampo()->getDescripcion().'", ';
                    $campos2 .= '"'.$c->getSignificadoCampo()->getDescripcion().'", ';
                }
            }           
            $campos .= " datos->'costo_hora_descuentos_permisos' AS costo_hora_recurso, ".
                        " datos->'dependencia' AS codigo_dependencia, ";
            $campos2 .= 'costo_hora_recurso, codigo_dependencia, ';
            $pivotes = array();
            foreach($Frm->getCampos() as $c){
                if ($c->getOrigenPivote()){
                    $pivotes[$c->getSignificadoCampo()->getCodigo()] = $em->getRepository('GridForm:Campo')->getOrigenPivote($c);
                }
            }
            $otros_campos = '';
            $nombre_pivote = array_pop(array_keys($pivotes));
            
            $piv1 = '';
            $piv2 = '';
            foreach (array_pop($pivotes) as $p){
                $cod = array_shift($p);
                $otros_campos .= "datos->'".$nombre_pivote."_". $cod."' AS ".'"'.strtolower($cod).'"'.", ";
                $piv1 .= "'".$cod."', ";
                $piv2 .= strtolower($cod).", ";
            }
            $piv1 = trim($piv1, ', ');
            $piv2 = trim($piv2, ', ');
            $sql1 = "SELECT *,  ((COALESCE(NULLIF(costo_hora_recurso,''),'0'))::numeric * (COALESCE(NULLIF(horas_centro,''),'0'))::numeric) AS CostoCentro "
                    . 'FROM (SELECT '.$campos2.' unnest(array['.$piv1.']) AS CentroCostos, '
                    . 'unnest(array['.$piv2.']) AS horas_centro FROM ( ';
            $sql2 = ') as A) AS B';
            $otros_campos = trim ($otros_campos, ', ');                        
            
        }
        
        
        $campos .= $otros_campos;
        
        $origenes = $this->getOrigenes($Frm->getOrigenDatos());

        $sql = $sql1 . " 
            SELECT $campos
            FROM costos.fila_origen_dato_".$codigo_fuente_costos.
            " WHERE id_origen_dato IN (" . implode(",", $origenes) . ")
            " . $sql2 ;
        // Leer desde la tabla temporal para poder establecer relaciones con otras tablas        
        $sql_resp = 'SELECT A.*, B.nombre AS  "Nombre dependencia", C.nombre AS "Establecimiento", C.codigo AS codigo_establecimiento
                FROM ('. $sql . ' ) AS A
                    INNER JOIN costos.estructura B ON (A.codigo_dependencia = B.codigo)
                    INNER JOIN costos.estructura C ON (substr(A.codigo_dependencia,1,2) = C.codigo)
                    ' ;
        
        try {
             $em->getConnection()->executeQuery($sql);
            return $em->getConnection()->executeQuery($sql_resp)->fetchAll();
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }
    
    /**
     * 
     * Reescribir la función cargarDatos para que haga las actualizaciones
     * correspondientes
     */
    protected function cargarDatos(Formulario $Frm) {
        $em = $this->getEntityManager();
        $this->area = $Frm->getAreaCosteo();
        
        if ($this->area == 'ga_variables' or $this->area == 'ga_distribucion'){
            $this->origenes = array($Frm->getId());
            $this->campo = 'id_formulario';
            $this->area = 'ga';
            
            //Cargar las dependencias que no estén en el mes y año elegido
            $sql = "INSERT INTO costos.fila_origen_dato_".strtolower($this->area)."(id_formulario, area_costeo, datos)
                    (SELECT ".$Frm->getId()." AS id_formulario, '".$Frm->getAreaCosteo()."' AS area_costeo, hstore(ARRAY['dependencia', 'mes', 'anio', 'establecimiento'], 
                            ARRAY[codigo , '".$this->parametros['mes']."', '".$this->parametros['anio']."', '".$this->parametros['establecimiento']."']) 
                        FROM costos.estructura
                        WHERE parent_id 
                            IN
                            (SELECT A.id FROM costos.estructura A
                                INNER JOIN costos.estructura B ON (A.parent_id = B.id )
                                WHERE B.codigo = '".$this->parametros['establecimiento']."'
                            )
                            AND (".$Frm->getId(). ", codigo, '".$this->parametros['mes']."', '".$this->parametros['anio']."', '".$this->parametros['establecimiento']."' )
                                NOT IN 
                                (SELECT id_formulario, datos->'dependencia', datos->'mes', datos->'anio', datos->'establecimiento'
                                    FROM costos.fila_origen_dato_ga 
                                    WHERE id_formulario = ".$Frm->getId()."
                                        AND datos->'establecimiento' = '".$this->parametros['establecimiento']."'
                                        AND datos->'anio' = '".$this->parametros['anio']."'
                                        AND datos->'mes' = '".$this->parametros['mes']."'
                                )                            
                    )";
            $em->getConnection()->executeQuery($sql);
        }        
        elseif ($this->area == 'ga_compromisosFinancieros'){
            $this->origenes = array($Frm->getId());
            $this->campo = 'id_formulario';
            $this->area = 'ga';
                                    
            //Cargar los contratos que no están en el año elegido
            $sql = "INSERT INTO costos.fila_origen_dato_".strtolower($this->area)."(id_formulario, area_costeo, datos)
                    (SELECT ".$Frm->getId()." AS id_formulario, '".$Frm->getAreaCosteo()."' AS area_costeo, 
                            hstore(
                                ARRAY['codigo_contrato', 'anio', 'establecimiento', 'descripcion_contrato',
                                        'criterio_distribucion', 'categoria_contrato'], 
                                ARRAY[A.codigo , '".$this->parametros['anio']."', '".$this->parametros['establecimiento']."', A.descripcion,
                                    B.descripcion, C.descripcion]
                            ) 
                        FROM costos.contratos_fijos_ga A 
                            INNER JOIN costos.criterios_distribucion_ga B ON (A.criteriodistribucion_id = B.id) 
                            INNER JOIN costos.categorias_contratos_fijos_ga C ON (A.categoria_id = C.id) 
                            INNER JOIN estructura_contratosfijosga D ON (A.id = D.contratosfijosga_id) 
                            INNER JOIN costos.estructura E ON (D.estructura_id = E.id)
                        WHERE E.codigo = '".$this->parametros['establecimiento']."'
                            AND (".$Frm->getId(). ", '".$Frm->getAreaCosteo(). "', A.codigo, '".$this->parametros['anio']."', '".$this->parametros['establecimiento']."' )
                                NOT IN 
                                (SELECT id_formulario, area_costeo, datos->'codigo_contrato', datos->'anio', datos->'establecimiento'
                                    FROM costos.fila_origen_dato_ga 
                                    WHERE id_formulario = ".$Frm->getId()."
                                        AND datos->'establecimiento' = '".$this->parametros['establecimiento']."'
                                        AND datos->'anio' = '".$this->parametros['anio']."'
                                )                            
                    )";
            $em->getConnection()->executeQuery($sql);
        }
    }
}