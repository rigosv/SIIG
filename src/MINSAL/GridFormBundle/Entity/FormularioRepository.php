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
    
    public function getDatosRAW(Formulario $Frm) {
        $em = $this->getEntityManager();
        $this->area = $Frm->getAreaCosteo();        
        
        $this->origenes = $this->getOrigenes($Frm->getOrigenDatos());
        $this->campo = 'id_origen_dato';
        
        /*if ($area == 'almacen_datos' or $area == 'calidad'){
            $this->cargarDatos($Frm);
        }*/
        
        //Cargar los campos del formulario para que estén disponibles por defecto
        $campos_ = array("datos->'anio' AS anio");
        foreach ($Frm->getCampos() as $c){
            $codigo = $c->getSignificadoCampo()->getCodigo();
            
            $campos_revisar = (($c->getOrigenPivote() == '')) ? array($codigo) : $this->getPivotes($c->getOrigenPivote(), $codigo);
            
            foreach ($campos_revisar as $c){                
                array_push($campos_, "datos->'".$c."' AS $c");
            }
        }
        
        $tabla =  ($this->area == 'almacen_datos' or $this->area == 'calidad') ? 'almacen_datos.repositorio' : 'costos.fila_origen_dato_'.strtolower($this->area);
        $sql = "
            SELECT ". implode(", ", $campos_) . " 
            FROM  $tabla
            WHERE id_formulario = '".$Frm->getId(). "'
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
        $this->orden = "ORDER BY datos->'es_poblacion' DESC, COALESCE(NULLIF(datos->'posicion', ''), '100000000')::numeric, datos->'descripcion_categoria_variable', datos->'descripcion_variable'";
        $em->getConnection()->executeQuery($sql);                
        
        $this->actualizarVariables($Frm->getId());
              
    }
    
    protected function actualizarVariables($frm_id){
        $em = $this->getEntityManager();
        
        $this->crearRangosAlertas($frm_id);
        
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
                        ||('\"alertas\"=>'||'\"'||COALESCE(D.alertas::varchar,'')||'\"')::hstore
                    FROM (SELECT texto_ayuda, es_poblacion, es_separador, posicion, nivel_indentacion, regla_validacion, 
                        replace(descripcion, '\"', '\\\"') AS descripcion, id_categoria_captura, id_tipo_control, codigo, id
                        FROM variable_captura ) AS A
                        INNER JOIN categoria_variable_captura B ON (A.id_categoria_captura = B.id)
                        LEFT JOIN costos.tipo_control C ON (A.id_tipo_control = C.id)
                        LEFT JOIN rangos_alertas_tmp D ON (A.id = D.variablecaptura_id)
                    WHERE almacen_datos.repositorio.datos->'codigo_variable' = A.codigo
                            AND almacen_datos.repositorio.id_formulario = $frm_id ";        
        $em->getConnection()->executeQuery($sql);
    }
    protected function crearRangosAlertas($frm_id){
        $em = $this->getEntityManager();
        //Los rangos de alertas
        $sql = "DROP TABLE IF EXISTS rangos_alertas_tmp";
        $em->getConnection()->executeQuery($sql);
        
        $sql = "SELECT DISTINCT ON (variablecaptura_id) variablecaptura_id, 
                    (select array_to_string(
                                array(
                                    SELECT COALESCE(limite_inferior::varchar,'')||'-'||COALESCE(limite_superior::varchar, '')||'-'||color  
                                        FROM variablecaptura_rangoalerta A 
                                            INNER JOIN rango_alerta B ON (A.rangoalerta_id = B.id) 
                                        WHERE A.variablecaptura_id = AA.variablecaptura_id
                                    ), ','
                                ) AS alertas
                    ) AS alertas 
                INTO TEMP rangos_alertas_tmp
                FROM variablecaptura_rangoalerta AA 
                    INNER JOIN variable_captura BB ON (AA.variablecaptura_id = BB.id) 
                WHERE formulario_id= $frm_id ";
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

        $params_string = $this->getParameterString($Frm, $parametros, $periodoIngreso->getId(), $tipo_periodo, $user);
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
        $datos = array();
        
        //Obtener los establecimientos
        $sql = "SELECT distinct on (datos->'establecimiento') datos->'establecimiento' AS id_establecimiento, C.nombre, 
                    COALESCE(C.nombre_corto, C.nombre) AS descripcion
                    FROM  almacen_datos.repositorio A
                        INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                        INNER JOIN costos.estructura C ON (A.datos->'establecimiento' = C.codigo::text)
                    WHERE B.area_costeo = 'calidad'
                        AND A.datos->'anio' = '$anio'
                        AND A.datos->'es_separador' != 'true'";
        $establecimientos = $em->getConnection()->executeQuery($sql)->fetchAll();
        
        foreach ($establecimientos as $est){
            $establecimiento = $est['id_establecimiento'];
            
            $frms = $this->getFormulariosEstablecimiento($establecimiento, $anio);
            $total_evaluacion = array('cumplimiento'=>0, 'no_cumplimiento'=>0);
            
            $formularios = array();
            foreach ($frms as $f) {
                $Frm = $em->getRepository('GridFormBundle:Formulario')->find($f);
                $formularios[$Frm->getId()]['form'] = $Frm;
                $grupos = $Frm->getGrupoFormularios();
                $datos_frm = (count($grupos) > 0 ) ? $grupos : array($Frm);

                foreach ($datos_frm as $ff){
                    $result = $this->getResultadoEvaluacion($ff, $establecimiento, $anio, $mes);
                    $total_evaluacion['cumplimiento'] = $total_evaluacion['cumplimiento'] + $result['total_cumplimiento'];
                    $total_evaluacion['no_cumplimiento'] = $total_evaluacion['no_cumplimiento'] + $result['total_no_cumplimiento'];
                }
                
            }
            
            $datos_['id_establecimiento'] = $est['id_establecimiento'];
            $datos_['category'] = $est['descripcion'];
            $datos_['nombre'] = $est['nombre'];
            //Obtener valores de evaluaciones externas, extraer la medición 
            //del último año ingresado para cada evaluación
            $sql = "SELECT C.descripcion AS categoria, B.descripcion AS tipo_evaluacion, 
                           A.anio, A.valor, B.unidad_medida
                        FROM evaluacion_externa A
                        INNER JOIN evaluacion_externa_tipo B ON (A.tipoevaluacion_id = B.id)
                        INNER JOIN evaluacion_categoria C ON (B.categoriaevaluacion_id = C.id)
                        WHERE ($establecimiento, tipoevaluacion_id, anio) 
                            IN 
                            (SELECT establecimiento_id, tipoevaluacion_id, MAX(anio) AS anio 
                                FROM evaluacion_externa 
                                GROUP BY establecimiento_id, tipoevaluacion_id
                            ) 
                        ORDER BY C.id, B.id, A.anio, A.valor";
            $evaluaciones = $em->getConnection()->executeQuery($sql)->fetchAll();
            $datos_['evaluaciones_externas'] = ( $evaluaciones);
            
            $datos_['total_cumplimiento'] = $total_evaluacion['cumplimiento'];
            $datos_['total_no_cumplimiento'] = $total_evaluacion['no_cumplimiento'];
            $datos_['total_aplicable'] = $total_evaluacion['cumplimiento'] + $total_evaluacion['no_cumplimiento'];
            $datos_['measure'] = (($datos_['total_aplicable'] > 0)) ? round($total_evaluacion['cumplimiento'] /  $datos_['total_aplicable'] * 100, 0 ): 0;
            $datos[] = $datos_;
        }
        
        return $datos;
    }
    
    public function getEvaluaciones($establecimiento, $periodo) {
        $em = $this->getEntityManager();
        $datos = array();
        $datos_ = array();
        list($anio, $mes) = explode('_', $periodo);
        
        $frms = $this->getFormulariosEstablecimiento($establecimiento, $anio);
        
        $formularios = array();
        foreach ($frms as $f) {
            $Frm = $em->getRepository('GridFormBundle:Formulario')->find($f);
            $total_evaluacion = array('cumplimiento'=>0, 'no_cumplimiento'=>0);
            $formularios[$Frm->getId()]['form'] = $Frm;
            $grupos = $Frm->getGrupoFormularios();
            $datos_frm = (count($grupos) > 0 ) ? $grupos : array($Frm);
            
            foreach ($datos_frm as $ff){
                $result = $this->getResultadoEvaluacion($ff, $establecimiento, $anio, $mes);
                $total_evaluacion['cumplimiento'] = $total_evaluacion['cumplimiento'] + $result['total_cumplimiento'];
                $total_evaluacion['no_cumplimiento'] = $total_evaluacion['no_cumplimiento'] + $result['total_no_cumplimiento'];
                
            }
            $datos_['codigo'] = $Frm->getCodigo();
            $datos_['tipo_evaluacion'] = $Frm->getFormaEvaluacion();
            $datos_['nombre_evaluacion'] = $Frm->getNombre();
            $datos_['axis'] = $Frm->getNombre();
            $datos_['descripcion'] = $Frm->getDescripcion();
            $datos_['descripcion'] = $Frm->getDescripcion();
            $datos_['meta'] = ($Frm->getMeta() > 0) ? number_format($Frm->getMeta()/100, 1) : 0;
            $datos_['periodo_lectura_datos'] = $Frm->getPeriodoLecturaDatos();
            $datos_['total_cumplimiento'] = $total_evaluacion['cumplimiento'];
            $datos_['total_no_cumplimiento'] = $total_evaluacion['no_cumplimiento'];
            $datos_['total_aplicable'] = $total_evaluacion['cumplimiento'] + $total_evaluacion['no_cumplimiento'];
            
            $datos_['measure'] = ($datos_['total_aplicable'] > 0)  ? round($total_evaluacion['cumplimiento'] /  $datos_['total_aplicable'] * 100, 0 ) : 0;
            $datos_['value'] = ($datos_['total_aplicable'] > 0 ) ? round($total_evaluacion['cumplimiento'] /  $datos_['total_aplicable'], 1 ) : 0;
            $datos_['brecha'] = ($datos_['meta'] > 0) ? ($datos_['meta'] * 100 - $datos_['measure'])/100 : 0;
            
            $datos[] = $datos_;
        }
        $datosGrafico2 = array();
        foreach($datos as $f){
            if ($f['tipo_evaluacion'] == 'lista_chequeo'){
                $datosGrafico2[] = $f;
            }
        }
        $resp[] = array('datos'=>$datos, 'datos_grafico2'=>$datosGrafico2);
        
        return $resp;
    }
    
    public function getListaCampos(Formulario $Frm, $array = true) {
        $campos = '';
        foreach ($Frm->getCampos() as $c){
            $piv = $c->getOrigenPivote();
            $codigoCampo = $c->getSignificadoCampo()->getCodigo();
            if ($piv != ''){
                $piv_ = json_decode($piv);
                //La parte de datos
                $campos .= ($array) ? "unnest(array[" : '';                
                foreach($piv_ as $p){
                    $alias = ($array) ? '' : ' AS "'.$p->descripcion.'" ';
                    $campos .= " datos->'".$codigoCampo."_".$p->id."'". $alias.", ";
                }
                $campos = ($array) ? trim($campos, ', ') : $campos;
                $campos .= ($array) ? "]) AS dato, " : '';
                
                //La parte del nombre del campo
                $campos .= ($array) ? "unnest(array[" : '';                
                foreach($piv_ as $p){
                    //$alias = ($array) ? '' : ' AS "'.$p->descripcion.'" ';
                    $campos .= "'$codigoCampo"."_".$p->id."', ";
                }
                $campos = ($array) ? trim($campos, ', ') : $campos;
                $campos .= ($array) ? "]) AS nombre_pivote, " : '';
            } else {
                $campos .= " datos->'$codigoCampo' AS $codigoCampo, ";
            }
        }
        return trim($campos, ', ');
    }
    
    protected function getFormulariosEstablecimiento($establecimiento, $anio) {
        $em = $this->getEntityManager();
        $resp = array();
        $sql = "SELECT * FROM (SELECT DISTINCT ON (id_formulario) COALESCE(id_formulario_sup, id_formulario) AS id_formulario, B.posicion
                FROM almacen_datos.repositorio A
                    INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                WHERE area_costeo = 'calidad'
                    AND A.datos->'establecimiento' = '$establecimiento'
                    AND A.datos->'anio' = '$anio'
                    AND A.datos->'es_separador' != 'true'
                ) AS AA
                ORDER BY posicion    
                ";
        
        $frms_ = $em->getConnection()->executeQuery($sql)->fetchAll();
        foreach ($frms_ as $f){
            $resp[$f['id_formulario']] = $f['id_formulario'];
        }
        
        return $resp;
    }
    
    protected function getDatosEvaluacion(Formulario $Frm, $establecimiento, $anio, $mes, $arreglo=true, $crear_tabla = true, $criteriosTodos = false, $eliminar_vacios = true) {
        $em = $this->getEntityManager();
        $periodo_lectura = '';
        $idFrm = $Frm->getId();
        $mes_ = '';
        if ($Frm->getPeriodoLecturaDatos() == 'mensual' and $mes != null){
            $periodo_lectura = " AND (A.datos->'mes')::integer = '$mes' ";
            $mes_ = " '$mes' AS mes, ";
        }
        $campos = $this->getListaCampos($Frm, $arreglo);
        
        $sql = "DROP TABLE IF EXISTS datos_tmp";
        $em->getConnection()->executeQuery($sql);
                
        $excluirCriterios = ($criteriosTodos) ? '': " AND A.datos->'es_separador' != 'true' ";

        $sql = "
                SELECT $campos, $anio AS anio, $mes_ '$establecimiento' as establecimiento, A.datos->'es_poblacion' AS es_poblacion,
                    A.datos->'codigo_tipo_control' AS tipo_control
                 INTO TEMP datos_tmp 
                 FROM almacen_datos.repositorio A
                 WHERE id_formulario = '$idFrm'
                    AND A.datos->'establecimiento' = '$establecimiento'
                    AND A.datos->'anio' = '$anio'
                    $excluirCriterios
                    $periodo_lectura
                 ";
        
        $em->getConnection()->executeQuery($sql);
        
        if ($eliminar_vacios){
            //Verificar si tiene la variable num_exp para obtener qué expedientes se ingresaron
            $sql = "SELECT codigo_variable FROM datos_tmp WHERE es_poblacion = 'true'";
            $cons = $em->getConnection()->executeQuery($sql);

            if ($cons->rowCount() > 0){
                //Quitar las columnas para las que no se ingresó número de expediente
                $sql = "DELETE FROM datos_tmp 
                            WHERE nombre_pivote 
                                NOT IN 
                                (SELECT nombre_pivote 
                                    FROM datos_tmp 
                                    WHERE es_poblacion = 'true' 
                                        AND dato is not null 
                                        AND dato != ''
                                )";
                $em->getConnection()->executeQuery($sql);
            }
            
            //Verificar si tiene la variable mes_check para dejar solo el que 
            // corresponde al mes que se está verificando
            $sql = "SELECT codigo_variable FROM datos_tmp WHERE nombre_pivote ~* 'mes_check_'";
            $cons = $em->getConnection()->executeQuery($sql);

            if ($cons->rowCount() > 0){
                //Quitar las columnas para las que no se ingresó número de expediente
                $sql = "DELETE FROM datos_tmp 
                            WHERE nombre_pivote 
                                NOT IN 
                                (SELECT nombre_pivote 
                                    FROM datos_tmp 
                                    WHERE 
                                        (nombre_pivote = 'mes_check_$mes' OR nombre_pivote = 'mes_check_0$mes')
                                        AND dato is not null 
                                        AND dato != ''
                                )";
                $em->getConnection()->executeQuery($sql);
            }
        }
        if (!$crear_tabla){
            $sql = "SELECT * FROM datos_tmp";
            return $em->getConnection()->executeQuery($sql);
        }
    }
    
    protected function getResultadoEvaluacion(Formulario $Frm, $establecimiento, $anio, $mes = null) {
        $em = $this->getEntityManager();
        
        $this->getDatosEvaluacion($Frm, $establecimiento, $anio, $mes);
        $idFrm = $Frm->getId();
        
        if ($Frm->getFormaEvaluacion() == 'lista_chequeo'){
            $sql = "SELECT CASE WHEN dato = 'true' THEN 1 ELSE 0 END AS cumplimiento, 
                        CASE WHEN tipo_control = 'checkbox' AND dato != 'true' THEN 1 
                             WHEN tipo_control = 'checkbox_3_states' AND dato = 'false' THEN 1
                             ELSE 0 
                        END AS no_cumplimiento 
                    FROM datos_tmp";
            $sql = "SELECT SUM(cumplimiento) AS total_cumplimiento, SUM(no_cumplimiento) AS total_no_cumplimiento
                FROM ( " . $sql . ") AS A";
        
            return array_pop($em->getConnection()->executeQuery($sql)->fetchAll());
        }         
        
        
    }
    
    public function getCriterios($establecimiento, $periodo, $formulario) {
        $em = $this->getEntityManager();        
        list($anio, $mes) = explode('_', $periodo);
        
        
        $Frm = $em->getRepository('GridFormBundle:Formulario')->findOneByCodigo($formulario); 
        
        
        $grupos = $Frm->getGrupoFormularios();
        $datos_frm = (count($grupos) > 0 ) ? $grupos : array($Frm);
        
        $sql_forms = '';
        foreach ($datos_frm as $ff){
            $datos = 'datos';
            $periodo_mensual = $ff->getPeriodoLecturaDatos() == 'mensual';
            $this->getDatosEvaluacion($ff, $establecimiento, $anio, $mes, true, true, true, false);

            //Verificar si tiene la variable de poblacion para obtener solo las columnas válidas        
            $sql = "SELECT codigo_variable FROM datos_tmp WHERE es_poblacion = 'true'";
            $cons = $em->getConnection()->executeQuery($sql);        
            if ($cons->rowCount() > 0){
                //Quitar las columnas para las que no se ingresó número de expediente
                $sql = "SELECT nombre_pivote FROM datos_tmp WHERE es_poblacion = 'true' AND (dato is null OR dato = '') ";
                $pivotes_borrar = $em->getConnection()->executeQuery($sql)->fetchAll();
                $piv_ = array();
                foreach ($pivotes_borrar as $c){
                    $piv_[] = $c['nombre_pivote'];
                }

                $pivotes_borrar = "'".implode("','", $piv_)."'";
                $datos = "delete(datos, ARRAY[$pivotes_borrar]) AS datos";
            }
            
            $frmId = $ff->getId();
            $mes_cadena = ($periodo_mensual) ? " AND (A.datos->'mes')::integer = '$mes' " : '';
            
            $this->ActualizarVariables($Frm->getId());
            
            $sql_forms .= "
                SELECT $datos, B.id, B.codigo, B.descripcion, B.forma_evaluacion
                    FROM  almacen_datos.repositorio A
                        INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                        INNER JOIN ctl_establecimiento_simmow C ON (A.datos->'establecimiento' = C.id::text)
                        INNER JOIN variable_captura D ON (A.datos->'codigo_variable' = D.codigo)
                    WHERE area_costeo = 'calidad'
                        AND A.datos->'anio' = '$anio'
                        $mes_cadena
                        AND A.datos->'establecimiento' = '$establecimiento'
                        AND B.id = '$frmId'
                            
                    UNION ALL ";
        }
        $sql_forms = trim($sql_forms, 'UNION ALL ');        
        
       
        $sql = "SELECT AA.id, AA.codigo, AA.descripcion, AA.forma_evaluacion , AA.datos
                FROM (
                    $sql_forms
                ) AS AA
                ORDER BY codigo, datos->'es_poblacion' DESC, COALESCE(NULLIF(datos->'posicion', ''), '100000000')::numeric, datos->'descripcion_categoria_variable', datos->'descripcion_variable'
                ;";
        try {
            return $em->getConnection()->executeQuery($sql)->fetchAll();
        } catch (\PDOException $e) {
            return $e->getMessage();
        }
    }
    
    
    public function getHistorialEstablecimiento($establecimiento, $periodo) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        $datos = array();
        $dato_ = array();
        
        //Obtener el puntaje de la evaluacion para cada mes
        
        //Todos los formularios posibles para el establecimiento
        $frms = $this->getFormulariosEstablecimiento($establecimiento, $anio);
        for($mes_i = 1; $mes_i<=$mes; $mes_i++){
            $formularios = array();
            $total_evaluacion = array('cumplimiento'=>0, 'no_cumplimiento'=>0);
            
            //Recorrer cada formulario
            foreach ($frms as $f) {
                $Frm = $em->getRepository('GridFormBundle:Formulario')->find($f);                
                $formularios[$Frm->getId()]['form'] = $Frm;
                $grupos = $Frm->getGrupoFormularios();
                $datos_frm = (count($grupos) > 0 ) ? $grupos : array($Frm);
                
                // Si tiene subformularios recorrer estos
                foreach ($datos_frm as $ff){
                    $result = $this->getResultadoEvaluacion($ff, $establecimiento, $anio, $mes_i);
                    $total_evaluacion['cumplimiento'] = $total_evaluacion['cumplimiento'] + $result['total_cumplimiento'];
                    $total_evaluacion['no_cumplimiento'] = $total_evaluacion['no_cumplimiento'] + $result['total_no_cumplimiento'];
                }                
            }
            $datos_['anio'] = $anio;
            $datos_['mes'] = $mes_i;
            $datos_['category'] = $mes_i.'/'.$anio;            
            $datos_['label'] = $datos_['category'];
            $datos_['date'] = $anio.'-'.$mes_i.'-'.'1';
            $datos_['total_cumplimiento'] = $total_evaluacion['cumplimiento'];
            $datos_['total_no_cumplimiento'] = $total_evaluacion['no_cumplimiento'];
            $datos_['total_aplicable'] = $total_evaluacion['cumplimiento'] + $total_evaluacion['no_cumplimiento'];
            $datos_['measure'] = ($datos_['total_aplicable'] > 0)  ? round($total_evaluacion['cumplimiento'] /  $datos_['total_aplicable'] * 100, 0 ) : 0;
            $datos_['value'] = ($datos_['total_aplicable'] > 0 ) ? round($total_evaluacion['cumplimiento'] /  $datos_['total_aplicable'], 0 ) : 0;            
            
            $datos[] = $datos_;
        }        
        return $datos;        
    }
}