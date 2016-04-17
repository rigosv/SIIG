<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\EntityRepository;
use MINSAL\GridFormBundle\Entity\Formulario;
use Symfony\Component\HttpFoundation\Request;

use MINSAL\GridFormBundle\Entity\PeriodoIngresoDatosFormulario;
/**
 * FormularioRepository
 * 
 */
class FormularioRepository extends EntityRepository {

    protected $parametros = array();
    protected $origenes = array();
    protected $campo = '';
    protected $orden = '';
    protected $area = '';
    
    public function getDatosCapturaDatos($FrmId) {
        
        $em = $this->getEntityManager();
        $Frm = $em->getRepository('GridFormBundle:Formulario')->find($FrmId);
        
        //$origenes = array($Frm->getId());
        //$campo = 'id_formulario';
        
        $sql = $Frm->getSqlLecturaDatos();        
        
        try {
            return $em->getConnection()->executeQuery($sql)->fetchAll();
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
        
    }
    
    public function getDatos(Formulario $Frm, $periodoIngreso, $tipo_periodo = null, Request $request, $user = null) {
        $em = $this->getEntityManager();
        $this->area = $Frm->getAreaCosteo();
        
        $parametros = $request->get('datos_frm');
        
        $this->orden = '';

        if ($tipo_periodo == null or $tipo_periodo == 'pu'){
            $periodoIngreso = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")->find($periodoIngreso);
        } elseif($tipo_periodo == 'pg'){
            $periodoIngreso = $em->getRepository("GridFormBundle:PeriodoIngresoGrupoUsuarios")->find($periodoIngreso);
        }
        
        $params_string = $this->getParameterString( $Frm, $parametros, $periodoIngreso->getId(), $tipo_periodo, $user);
        $this->origenes = $this->getOrigenes($Frm->getOrigenDatos());
        $this->campo = 'id_origen_dato';
        
        if ($this->area == 'almacen_datos' or $this->area == 'calidad'){
            $this->cargarDatos($Frm);
        }
        
        $tabla =  ($this->area == 'almacen_datos' or $this->area == 'calidad') ? 'almacen_datos.repositorio' : 'costos.fila_origen_dato_'.strtolower($this->area);
        $sql = "
            SELECT datos
            FROM  $tabla
            WHERE $this->campo IN (" . implode(',', $this->origenes) . ")
                $params_string
                $this->orden
            ;";
        try {
            return $em->getConnection()->executeQuery($sql)->fetchAll();
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }
    
    /**
     * 
     * @param Formulario $Frm
     * Actualiza los datos cargando los posibles cambios que puedan existir 
     * esto se debe hacer antes de que los datos sean leidos
     */
    protected function cargarDatos(Formulario $Frm) {
        $em = $this->getEntityManager();
        
        $this->origenes = array($Frm->getId());
        $this->campo = 'id_formulario';
        
        $dependencia2 = ''; $dependencia3='';
        if (array_key_exists('dependencia', $this->parametros)){
            $dependencia2 = "'". $this->parametros['dependencia'] . "' , ";
            $dependencia3 = " datos->'dependencia', ";
        }

        //Si es mensual agregar el mes a la consulta
        $mes_val = ""; $mes_txt2 = ""; $mes_condicion = "";
        if ($Frm->getPeriodoLecturaDatos() == 'mensual'){
            $mes_val = " '" . $this->parametros['mes'] . "', ";
            $mes_txt2 = " datos->'mes',";
            $mes_condicion = " AND datos->'mes' = '" . $this->parametros['mes'] . "' ";
        }
        
        $datosIni = $this->getCamposInicializar($Frm);
        
        //Cargar las variables que no están en el año elegido
        $sql = "INSERT INTO almacen_datos.repositorio (id_formulario, datos)
                (SELECT ".$Frm->getId()." AS id_formulario, 
                        hstore(
                            ARRAY[" . implode(", ", $datosIni['llaves']) . "], 
                            ARRAY[" . implode(", ", $datosIni['valores']) . "]
                        ) 
                    FROM variable_captura A 
                        INNER JOIN categoria_variable_captura B ON (A.id_categoria_captura = B.id)
                    WHERE 
                         (".$Frm->getId(). ", A.codigo, '".$this->parametros['anio']."', $mes_val $dependencia2 '".$this->parametros['establecimiento']."' )
                            NOT IN 
                            (SELECT id_formulario,  datos->'codigo_variable', datos->'anio', $mes_txt2 $dependencia3 datos->'establecimiento'
                                FROM almacen_datos.repositorio
                                WHERE id_formulario = ".$Frm->getId()."
                                    AND datos->'establecimiento' = '".$this->parametros['establecimiento']."'
                                    AND datos->'anio' = '".$this->parametros['anio']."'
                                    $mes_condicion
                            )
                        AND A.formulario_id =  ".$Frm->getId()."
                )";
        $this->orden = "ORDER BY datos->'es_poblacion' DESC, COALESCE(NULLIF(datos->'posicion', ''), '100000000')::integer, datos->'descripcion_categoria_variable', datos->'descripcion_variable'";
        $em->getConnection()->executeQuery($sql);

        //Actualizar los datos de las variables ya existentes
        $sql = " UPDATE almacen_datos.repositorio 
                    SET datos = datos ||('\"ayuda\"=>'||'\"'||COALESCE(A.texto_ayuda,'')||'\"')::hstore 
                        ||('\"codigo_categoria_variable\"=>'||'\"'||COALESCE(B.codigo,'')||'\"')::hstore 
                        ||('\"descripcion_categoria_variable\"=>'||'\"'||COALESCE(B.descripcion,'')||'\"')::hstore
                        ||('\"es_poblacion\"=>'||'\"'||COALESCE(A.es_poblacion::varchar,'')||'\"')::hstore
                        ||('\"es_separador\"=>'||'\"'||COALESCE(A.es_separador::varchar,'')||'\"')::hstore
                        ||('\"posicion\"=>'||'\"'||COALESCE(A.posicion::varchar,'')||'\"')::hstore
                        ||('\"nivel_indentacion\"=>'||'\"'||COALESCE(A.nivel_indentacion::varchar,'')||'\"')::hstore
                        ||('\"descripcion_variable\"=>'||'\"'|| COALESCE(A.descripcion::varchar, '') ||'\"')::hstore
                        ||('\"regla_validacion\"=>'||'\"'||COALESCE(A.regla_validacion::varchar,'')||'\"')::hstore
                        ||('\"codigo_tipo_control\"=>'||'\"'||COALESCE(C.codigo::varchar,'')||'\"')::hstore
                    FROM (SELECT texto_ayuda, es_poblacion, es_separador, posicion, nivel_indentacion, regla_validacion, 
                        replace(descripcion, '\"', '\\\"') AS descripcion, id_categoria_captura, id_tipo_control, codigo
                        FROM variable_captura ) AS A
                        INNER JOIN categoria_variable_captura B ON (A.id_categoria_captura = B.id)
                        LEFT JOIN costos.tipo_control C ON (A.id_tipo_control = C.id)
                    WHERE almacen_datos.repositorio.datos->'codigo_variable' = A.codigo";        
        $em->getConnection()->executeQuery($sql);      
    }
    /**
     * 
     * @param type $Frm 
     * 
     */
    protected function getCamposInicializar(Formulario $Frm) {
        $dependencia1 = ''; $dependencia2 = ''; 
        if (array_key_exists('dependencia', $this->parametros)){
            $dependencia1 = "'dependencia'";
            $dependencia2 = "'". $this->parametros['dependencia'] . "' , ";
        }

        //Si es mensual agregar el mes a la consulta
        $mes_txt = ""; $mes_val = "";
        if ($Frm->getPeriodoLecturaDatos() == 'mensual'){
            $mes_txt = "'mes'";
            $mes_val = "'" . $this->parametros['mes'] . "'";
        }

        // Inicializar todas las variables dentro del formulario
        $llaves = array("'codigo_variable'", "'anio'", "'establecimiento'",  "'descripcion_variable'",
                        "'codigo_categoria_variable'", "'descripcion_categoria_variable'", "'es_poblacion'", "'posicion'", 
                        "'es_separador'", "'nivel_indentacion'", "'regla_validacion'");
        if ($mes_txt != '') {array_push ($llaves, $mes_txt);}
        if ($dependencia1 != '') {array_push ($llaves, $dependencia1);}

        $valores = array("A.codigo" , "'".$this->parametros['anio']."'", "'".$this->parametros['establecimiento']."'",  "A.descripcion",
                                "B.codigo", "B.descripcion",  "COALESCE(A.es_poblacion::varchar,'')", "COALESCE(A.posicion::varchar,'0')", 
                                "COALESCE(A.es_separador::varchar,'')", "COALESCE(A.nivel_indentacion::varchar,'0')", 
                                "COALESCE(A.regla_validacion::varchar,'')");
        if ($mes_val != '') {array_push ($valores, $mes_val);}
        if ($dependencia2 != '') {array_push ($valores, $dependencia2);}

        //Cargar los campos del formulario para que estén disponibles por defecto
        foreach ($Frm->getCampos() as $c){
            $codigo = $c->getSignificadoCampo()->getCodigo();
            
            $campos_revisar = (($c->getOrigenPivote() == '')) ? array($codigo) : $this->getPivotes($c->getOrigenPivote(), $codigo);
            
            foreach ($campos_revisar as $c){
                if (!in_array("'$c'", $llaves)){
                    array_push($llaves, "'".$c."'");
                    array_push($valores, "''");
                }
            }
        }
        return array('llaves'=> $llaves, 'valores'=> $valores);
    }
    
    protected function getPivotes($pivotesCadena, $codigoCampo) {
        $resp = array();
        foreach(json_decode($pivotesCadena, true) as $d){        
            array_push($resp, $codigoCampo.'_'.(string)$d['id']);
        }
        return $resp;
    }
    
    
    public function tipoDatoPorFila(Formulario $Frm) {
        $variables = $Frm->getVariables();
        foreach ($variables as $v){
            if ($v->getTipoControl() != null){
                return true;
            }
        }
        return false;
    }
    
    private function getOrigenes($origen) {
        $origenes = array();
        if ($origen != null){
            if ($origen->getEsFusionado()) {
                foreach ($origen->getFusiones() as $of) {
                    $origenes[] = $of->getId();
                }
            } else {
                $origenes[] = $origen->getId();
            }
        }
        return $origenes;
    }

    private function getParameterString(Formulario $Frm, $parametros, $periodoIngreso = null, $tipo_periodo = null, $user = null ) {
        $params_string = '';
        $em = $this->getEntityManager();
        if ($tipo_periodo == null or $tipo_periodo == 'pu'){
            $periodoIngreso = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")->find($periodoIngreso);
        } elseif($tipo_periodo == 'pg'){
            $periodoIngreso = $em->getRepository("GridFormBundle:PeriodoIngresoGrupoUsuarios")->find($periodoIngreso);
        }        
        if ($periodoIngreso !=  null ){
            if ($Frm->getPeriodoLecturaDatos() == 'mensual')
                $this->parametros['mes'] = $periodoIngreso->getPeriodo()->getMes();
            $this->parametros['anio'] = $periodoIngreso->getPeriodo()->getAnio();
            
            if ($tipo_periodo == 'pg'){
                $unidad = $user->getEstablecimientoPrincipal();
            } else {                
                $unidad = $periodoIngreso->getUnidad();
            }
            if ($unidad->getNivel() == 1 ) {
                $this->parametros['establecimiento'] = $unidad->getCodigo();
            } elseif ($unidad->getNivel() == 2 ) {
                $this->parametros['establecimiento'] = $unidad->getParent()->getCodigo();
                $this->parametros['dependencia'] = $unidad->getId();
            } elseif ($unidad->getNivel() == 3 ) {
                $this->parametros['establecimiento'] = $unidad->getParent()->getParent()->getCodigo();
                $this->parametros['dependencia'] = $unidad->getCodigo();
            }
        }
        foreach ($this->parametros as $key => $value) {
            $params_string .= " AND datos->'" . $key . "' = '" . $value . "' ";
        }
        
        return $params_string;
    }

    public function setDatos(Formulario $Frm, $periodoIngreso, $tipo_periodo = null, Request $request, $user = null) {
        $em = $this->getEntityManager();
        $parametros = $request->get('datos_frm');
        
        if ($tipo_periodo == null or $tipo_periodo == 'pu'){
            $periodoIngreso = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")->find($periodoIngreso);
        } elseif($tipo_periodo == 'pg'){
            $periodoIngreso = $em->getRepository("GridFormBundle:PeriodoIngresoGrupoUsuarios")->find($periodoIngreso);
        }

        $params_string = $this->getParameterString($parametros, $periodoIngreso->getId(), $tipo_periodo, $user);
        $area = $Frm->getAreaCosteo();
        if ($area != 'ga_variables' and $area != 'ga_compromisosFinancieros' and $area != 'ga_distribucion' and $area != 'almacen_datos' and $area != 'calidad'){
            $origenes = $this->getOrigenes($Frm->getOrigenDatos());
            $campo = 'id_origen_dato';
            $tabla = 'costos.fila_origen_dato_'.strtolower($area);
        } else {
            $origenes = array($Frm->getId());
            $campo = 'id_formulario';
            $tabla =  ($area == 'almacen_datos' or $area == 'calidad') ? 'almacen_datos.repositorio' : 'costos.fila_origen_dato_ga';
        }

        $datosObj = json_decode($request->get('fila'));
        $datos = str_replace(array('{', '}', ':', 'null'), array('', '', '=>', '""'), $request->get('fila'));
        // eliminar mensajes de ayuda que vienen separados por ||
        //$datos = preg_replace('/\|\|[\s\S]*j?"/', '"', $datos);
        
        //Cambiar formato de fecha
        $datos = preg_replace('/([0-9]{4})-([0-9]{2})-([0-9]{2})T[0-9]{2}=>[0-9]{2}=>[0-9]{2}.[0-9]{3}Z/', '${3}/${2}/${1}', $datos);

        $params_string .= "AND datos->'" . $request->get('pk') . "' = '" . $datosObj->{$request->get('pk')} . "'";
                
        $sql = "
            UPDATE $tabla
            SET datos = datos || '" . $datos . "'::hstore
            WHERE $campo IN (" . implode(',', $origenes) . ")
                $params_string
            ;";

        try {
            $em->getConnection()->executeQuery($sql);
            // Mandar los datos actualizados, para que muestre en el grid
            // los campos calculados por el procedimiento de la base de datos
            $sql = "
            SELECT datos 
            FROM $tabla
            WHERE $campo IN (" . implode(',', $origenes) . ")
                $params_string                
            ;";
            return $em->getConnection()->executeQuery($sql)->fetchAll();
            //return true;
        } catch (\PDOException $e) {
            return false;
        }
    }
    
    public function getPeriodosEvaluacion() {
        $em = $this->getEntityManager();
        
        $sql = " SELECT anio, mes::integer, anio||'_'||(mes::integer) AS periodo, 
            (mes::integer)||'/'||anio AS etiqueta
            FROM (
                
                SELECT A.datos->'anio' AS anio, A.datos->'mes' AS mes
                FROM  almacen_datos.repositorio A
                    INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                    INNER JOIN ctl_establecimiento_simmow C ON (A.datos->'establecimiento' = C.id::text)
                WHERE B.area_costeo = 'calidad'
                    AND B.periodo_lectura_datos = 'mensual'
                    AND A.datos->'anio' != ''
                    AND A.datos->'mes' != ''
                    AND A.datos->'es_separador' != 'true'
                
                UNION ALL
                
                SELECT BB.anio, BB.mes 
                    FROM (
                        SELECT A.datos->'anio' AS anio, 
                            unnest(array['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12']) AS mes, 
                            unnest(array[datos->'mes_check_01', datos->'mes_check_02',
                                datos->'mes_check_03' , datos->'mes_check_04' ,
                                datos->'mes_check_05' , datos->'mes_check_06' ,
                                datos->'mes_check_07' , datos->'mes_check_08' , 
                                datos->'mes_check_09' , datos->'mes_check_10' , 
                                datos->'mes_check_11' , datos->'mes_check_12']
                                ) AS dato
                            FROM  almacen_datos.repositorio A
                                INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                                INNER JOIN ctl_establecimiento_simmow C ON (A.datos->'establecimiento' = C.id::text)
                            WHERE B.area_costeo = 'calidad'
                                AND B.periodo_lectura_datos = 'anual'
                                AND A.datos->'anio' != ''
                                AND A.datos->'es_separador' != 'true'
                        ) AS BB
                        WHERE BB.dato = 'true'
                ) AS AA
                GROUP BY anio, mes::integer
                ORDER BY anio DESC, mes::integer DESC
            ;";
        try {
            return $em->getConnection()->executeQuery($sql)->fetchAll();
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }
    
    public function getEstablecimientosEvaluados($periodo) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $sql = " SELECT id_establecimiento, category, nombre, total_cumplimiento, 
            total_no_cumplimiento, total_aplicable, round(((total_cumplimiento::numeric/total_aplicable::numeric)::numeric * 100 )) AS measure
            FROM 
            (
            SELECT id_establecimiento, id_establecimiento AS category, establecimiento AS nombre,
                SUM(cumplimiento) AS total_cumplimiento, 
                SUM(no_cumplimiento) AS total_no_cumplimiento, 
                SUM(cumplimiento) + SUM(no_cumplimiento) AS total_aplicable
            FROM (
                SELECT AA.id_establecimiento, AA,establecimiento,
                    CASE WHEN AA.dato = 'true' THEN 1 END AS cumplimiento, 
                    CASE WHEN AA.dato = 'false' OR AA.dato = '' THEN 1 END AS no_cumplimiento
                FROM (
                    SELECT C.id AS id_establecimiento, C.descripcion AS establecimiento,
                        unnest(array[datos->'num_expe_1', datos->'num_expe_2',
                            datos->'num_expe_3' , datos->'num_expe_4' ,
                            datos->'num_expe_5' , datos->'num_expe_6' ,
                            datos->'num_expe_7' , datos->'num_expe_8' , 
                            datos->'num_expe_9' , datos->'num_expe_10' ]
                            ) AS dato
                    FROM  almacen_datos.repositorio A
                        INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                        INNER JOIN ctl_establecimiento_simmow C ON (A.datos->'establecimiento' = C.id::text)
                    WHERE B.area_costeo = 'calidad'
                        AND B.periodo_lectura_datos = 'mensual'
                        AND A.datos->'anio' = '$anio'
                        AND (A.datos->'mes')::integer = '$mes'
                        AND A.datos->'es_separador' != 'true'

                    UNION ALL

                    SELECT BB.id_establecimiento, BB.establecimiento, BB.dato 
                        FROM (
                            SELECT C.id AS id_establecimiento, C.descripcion AS establecimiento, 
                                A.datos->'anio' AS anio, 
                                unnest(array['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12']) AS mes, 
                                unnest(array[datos->'mes_check_01', datos->'mes_check_02',
                                    datos->'mes_check_03' , datos->'mes_check_04' ,
                                    datos->'mes_check_05' , datos->'mes_check_06' ,
                                    datos->'mes_check_07' , datos->'mes_check_08' , 
                                    datos->'mes_check_09' , datos->'mes_check_10' , 
                                    datos->'mes_check_11' , datos->'mes_check_12']
                                    ) AS dato
                                FROM  almacen_datos.repositorio A
                                    INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                                    INNER JOIN ctl_establecimiento_simmow C ON (A.datos->'establecimiento' = C.id::text)
                                WHERE B.area_costeo = 'calidad'
                                    AND B.periodo_lectura_datos = 'anual'
                                    AND A.datos->'anio' = '$anio'
                                    AND A.datos->'es_separador' != 'true'
                            ) AS BB
                        WHERE (BB.mes)::integer = '$mes'
                    ) AS AA
            ) AS AAA
            GROUP BY id_establecimiento, establecimiento
            HAVING (SUM(cumplimiento) + SUM(no_cumplimiento)) > 0
            ) AS A1            
            ORDER BY total_cumplimiento DESC
                ;";
        try {
            return $em->getConnection()->executeQuery($sql)->fetchAll();
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }
    
    public function getEvaluaciones($establecimiento, $periodo) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $sql = " SELECT codigo, nombre_evaluacion, category AS axis, descripcion, round((meta/100),1) AS meta, periodo_lectura_datos,
            total_cumplimiento, total_no_cumplimiento, total_aplicable, 
            round(((total_cumplimiento::numeric/total_aplicable::numeric)::numeric * 100 )) AS measure,
            round(((total_cumplimiento::numeric/total_aplicable::numeric)::numeric),2) AS value,
            (meta/100 - total_cumplimiento::numeric/total_aplicable::numeric) AS brecha
            FROM 
            (
                SELECT codigo, nombre_evaluacion, nombre_evaluacion AS category, descripcion, meta,
                    periodo_lectura_datos, 
                    COUNT(cumplimiento) AS total_cumplimiento, 
                    COUNT(no_cumplimiento) AS total_no_cumplimiento, 
                    COUNT(cumplimiento) + COUNT(no_cumplimiento) AS total_aplicable
                FROM (
                    SELECT AA.codigo, AA.nombre_evaluacion, AA.descripcion, COALESCE(AA.meta, 0)::numeric AS meta, AA.periodo_lectura_datos,
                        CASE WHEN AA.dato = 'true' THEN 1 END AS cumplimiento, 
                        CASE WHEN AA.dato = 'false' OR AA.dato = '' THEN 1 END AS no_cumplimiento
                    FROM (

                        SELECT B.codigo, B.nombre AS nombre_evaluacion, B.descripcion, COALESCE(B.meta, 0)::numeric AS meta, B.periodo_lectura_datos,
                            unnest(array[datos->'num_expe_1', datos->'num_expe_2',
                                datos->'num_expe_3' , datos->'num_expe_4' ,
                                datos->'num_expe_5' , datos->'num_expe_6' ,
                                datos->'num_expe_7' , datos->'num_expe_8' , 
                                datos->'num_expe_9' , datos->'num_expe_10' ]
                                ) AS dato
                        FROM  almacen_datos.repositorio A
                            INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                            INNER JOIN ctl_establecimiento_simmow C ON (A.datos->'establecimiento' = C.id::text)
                        WHERE area_costeo = 'calidad'
                            AND B.periodo_lectura_datos = 'mensual'
                            AND A.datos->'anio' = '$anio'
                            AND (A.datos->'mes')::integer = '$mes'
                            AND A.datos->'establecimiento' = '$establecimiento'
                            AND A.datos->'es_separador' != 'true'

                        UNION ALL

                        SELECT BB.codigo, BB.nombre_evaluacion, BB.descripcion, COALESCE(BB.meta, 0)::numeric AS meta, BB.periodo_lectura_datos,
                            BB.dato 
                            FROM (
                                SELECT B.codigo, B.nombre AS nombre_evaluacion, B.descripcion, COALESCE(B.meta, 0)::numeric AS meta, B.periodo_lectura_datos,
                                    A.datos->'anio' AS anio, 
                                    unnest(array['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12']) AS mes, 
                                    unnest(array[datos->'mes_check_01', datos->'mes_check_02',
                                        datos->'mes_check_03' , datos->'mes_check_04' ,
                                        datos->'mes_check_05' , datos->'mes_check_06' ,
                                        datos->'mes_check_07' , datos->'mes_check_08' , 
                                        datos->'mes_check_09' , datos->'mes_check_10' , 
                                        datos->'mes_check_11' , datos->'mes_check_12']
                                        ) AS dato
                                    FROM  almacen_datos.repositorio A
                                        INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                                        INNER JOIN ctl_establecimiento_simmow C ON (A.datos->'establecimiento' = C.id::text)
                                    WHERE area_costeo = 'calidad'
                                        AND B.periodo_lectura_datos = 'anual'
                                        AND A.datos->'anio' = '$anio'
                                        AND A.datos->'establecimiento' = '$establecimiento'
                                        AND A.datos->'es_separador' != 'true'
                                ) AS BB
                            WHERE (BB.mes)::integer = '$mes'
                        ) AS AA
                ) AS AAA
                GROUP BY codigo, nombre_evaluacion, descripcion, meta, periodo_lectura_datos
                HAVING COUNT(cumplimiento) + COUNT(no_cumplimiento) > 0
            ) AS A1
            ORDER BY total_cumplimiento DESC
                ";
        try {
            return $em->getConnection()->executeQuery($sql)->fetchAll();
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }
    
    public function getCriterios($establecimiento, $periodo, $formulario) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $sql = "SELECT AA.datos
                FROM (
                    SELECT datos                        
                    FROM  almacen_datos.repositorio A
                        INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                        INNER JOIN ctl_establecimiento_simmow C ON (A.datos->'establecimiento' = C.id::text)
                    WHERE area_costeo = 'calidad'
                        AND B.periodo_lectura_datos = 'mensual'
                        AND A.datos->'anio' = '$anio'
                        AND (A.datos->'mes')::integer = '$mes'
                        AND A.datos->'establecimiento' = '$establecimiento'
                        AND B.codigo = '$formulario'

                    UNION ALL

                    SELECT datos
                        FROM  almacen_datos.repositorio A
                            INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                            INNER JOIN ctl_establecimiento_simmow C ON (A.datos->'establecimiento' = C.id::text)
                        WHERE area_costeo = 'calidad'
                            AND B.periodo_lectura_datos = 'anual'
                            AND A.datos->'anio' = '$anio'
                            AND A.datos->'establecimiento' = '$establecimiento'
                            AND B.codigo = '$formulario'
                ) AS AA
                ORDER BY datos->'es_poblacion' DESC, COALESCE(NULLIF(datos->'posicion', ''), '100000000')::integer, datos->'descripcion_categoria_variable', datos->'descripcion_variable'
                ;";
        try {
            return $em->getConnection()->executeQuery($sql)->fetchAll();
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }
    
    
    public function getHistorialEstablecimiento($establecimiento) {
        $em = $this->getEntityManager();
        
        $sql = " SELECT anio, mes, category, total_cumplimiento, mes||'/'||anio AS label, anio||'-'||mes||'-'||1 AS date,
            total_no_cumplimiento, total_aplicable, 
            round(((total_cumplimiento::numeric/total_aplicable::numeric)::numeric * 100 )) AS measure,
            round(((total_cumplimiento::numeric/total_aplicable::numeric)::numeric * 100 )) AS value
            FROM 
            (
            SELECT anio, mes, mes||'/'||anio AS category, 
                SUM(cumplimiento) AS total_cumplimiento, 
                SUM(no_cumplimiento) AS total_no_cumplimiento, 
                SUM(cumplimiento) + SUM(no_cumplimiento) AS total_aplicable
            FROM (
                SELECT AA.anio, AA.mes,
                    CASE WHEN AA.dato = 'true' THEN 1 END AS cumplimiento, 
                    CASE WHEN AA.dato = 'false' OR AA.dato = '' THEN 1 END AS no_cumplimiento
                FROM (
                    SELECT A.datos->'anio' as anio, A.datos->'mes' AS mes,
                        unnest(array[datos->'num_expe_1', datos->'num_expe_2',
                            datos->'num_expe_3' , datos->'num_expe_4' ,
                            datos->'num_expe_5' , datos->'num_expe_6' ,
                            datos->'num_expe_7' , datos->'num_expe_8' , 
                            datos->'num_expe_9' , datos->'num_expe_10' ]
                            ) AS dato
                    FROM  almacen_datos.repositorio A
                        INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                        INNER JOIN ctl_establecimiento_simmow C ON (A.datos->'establecimiento' = C.id::text)
                    WHERE B.area_costeo = 'calidad'
                        AND B.periodo_lectura_datos = 'mensual'
                        AND A.datos->'establecimiento' = '$establecimiento'
                        AND A.datos->'es_separador' != 'true'

                    UNION ALL

                    SELECT BB.anio, BB.mes, BB.dato 
                        FROM (
                            SELECT C.id AS id_establecimiento, C.descripcion AS establecimiento, 
                                A.datos->'anio' AS anio, 
                                unnest(array['1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12']) AS mes, 
                                unnest(array[datos->'mes_check_01', datos->'mes_check_02',
                                    datos->'mes_check_03' , datos->'mes_check_04' ,
                                    datos->'mes_check_05' , datos->'mes_check_06' ,
                                    datos->'mes_check_07' , datos->'mes_check_08' , 
                                    datos->'mes_check_09' , datos->'mes_check_10' , 
                                    datos->'mes_check_11' , datos->'mes_check_12']
                                    ) AS dato
                                FROM  almacen_datos.repositorio A
                                    INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                                    INNER JOIN ctl_establecimiento_simmow C ON (A.datos->'establecimiento' = C.id::text)
                                WHERE B.area_costeo = 'calidad'
                                    AND B.periodo_lectura_datos = 'anual'
                                    AND A.datos->'establecimiento' = '$establecimiento'
                                    AND A.datos->'es_separador' != 'true'
                            ) AS BB
                    ) AS AA
            ) AS AAA
            GROUP BY anio, mes
            HAVING (SUM(cumplimiento) + SUM(no_cumplimiento)) > 0
            ) AS A1            
            ORDER BY anio, mes DESC
                ;";
        try {
            return $em->getConnection()->executeQuery($sql)->fetchAll();
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }
}