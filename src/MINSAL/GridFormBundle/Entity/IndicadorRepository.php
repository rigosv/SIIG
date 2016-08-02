<?php

namespace MINSAL\GridFormBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\Request;
use MINSAL\GridFormBundle\Entity\FormularioRepository;
use MINSAL\GridFormBundle\Entity\Formulario;
use MINSAL\GridFormBundle\Entity\Indicador;

/**
 * IndicadorRepository
 * 
 */
class IndicadorRepository extends EntityRepository {

    protected $meses = array(
                1=>'Ene',
                2=>'Feb',
                3=>'Mar',
                4=>'Abr',
                5=>'May',
                6=>'Jun',
                7=>'Jul',
                8=>'Ago',
                9=>'Sep',
                10=>'Oct',
                11=>'Nov',
                12=>'Dic'
                );
    /**
     * 
     * @param type $periodo
     * @return type
     * Recupera los indicadores que son por lista de chequeo
     * 
     */
    public function getIndicadoresEvaluadosListaChequeo($periodo) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        $datos = array();
        $calificaciones = array();
        
        $sql = "SELECT B.posicion, A.codigo, A.descripcion, A.forma_evaluacion,  
                                A.porcentaje_aceptacion, 
                                B.descripcion descripcion_estandar, B.periodo_lectura_datos, 
                                B.id as estandar_id, B.codigo as codigo_estandar, A.id AS indicador_id,
                                B.forma_evaluacion AS tipo_evaluacion, B.nombre AS nombre_evaluacion, 
                                B.meta, B.periodo_lectura_datos, B.posicion
                    FROM indicador A
                        INNER JOIN costos.formulario B ON (A.estandar_id = B.id)
                    WHERE B.forma_evaluacion = 'lista_chequeo'
                    ORDER BY posicion, codigo_estandar, codigo
                    ";
        
        $indicadores = $em->getConnection()->executeQuery($sql)->fetchAll();
        foreach ($indicadores as $ind){
            $Frm = $em->getRepository('GridFormBundle:Formulario')->find($ind['estandar_id']);
            $eval_ = $this->getDatosEvaluacionListaChequeo($Frm, $periodo, $ind);
            
            $calificacion = 0;
            $eval = array();
            foreach($eval_ as $e){
                $calificacion += $e['calificacion'];
            }
            $calificacionIndicador = (count($eval_) > 0) ? number_format(($calificacion / count($eval_)),2) : 0;
            foreach($eval_ as $e){
                $f = $e;
                $f['calificacion_indicador'] = $calificacionIndicador;
                $eval[] = $f;
            }
            $sql = "SELECT color FROM rangos_alertas_generales 
                        WHERE $calificacionIndicador >= limite_inferior
                            AND $calificacionIndicador <= limite_superior";
            $cons = $em->getConnection()->executeQuery($sql);
            $color = ($cons->rowCount() > 0 ) ? $cons->fetch(): array('color'=>'#0EAED8');
            
            $datos[] = array('descripcion_estandar'=>$ind['descripcion_estandar'],  
                            'descripcion_indicador'=>$ind['descripcion'] ,
                            'codigo_estandar' => $ind['codigo_estandar'],
                            'tipo_evaluacion' => $ind['tipo_evaluacion'],
                            'nombre_evaluacion' => $ind['nombre_evaluacion'],
                            'meta' => $ind['meta'],
                            'periodo_lectura_datos' => $ind['periodo_lectura_datos'],
                            'codigo_indicador'=>$ind['codigo'] ,
                            'calificacion' => $calificacionIndicador,
                            'evaluacion' => $eval,
                            'category'=> $ind['codigo'] ,
                            'measure' => $calificacionIndicador,
                            'color' => $color['color']
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
    
    /**
     * 
     * @param type $periodo
     * @return type
     * Recupera los indicadores que tienen indicadores hijos asociados
     * datos numéricos
     */
    public function getIndicadoresEvaluadosNumericos($periodo) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $this->prepararDatosEvaluacionNumerica($periodo);

        $datos = array();
        $calificaciones = array();
                
        $sql = "SELECT B.id, B.codigo AS codigo_indicador, B.descripcion AS descripcion_indicador,
                    A.calificacion,(SELECT color 
                                    FROM indicador_rangoalerta AA 
                                        INNER JOIN rango_alerta BB ON (AA.rangoalerta_id = BB.id)
                                    WHERE A.calificacion BETWEEN COALESCE(limite_inferior, -100000) AND COALESCE(limite_superior, 1000000)
                                    ) AS color, 
                                array(
                                    SELECT COALESCE(limite_inferior::varchar,'')||'-'||COALESCE(limite_superior::varchar, '')||'-'||color  
                                        FROM indicador_rangoalerta AA 
                                            INNER JOIN rango_alerta BB ON (AA.rangoalerta_id = BB.id) 
                                        WHERE AA.indicador_id = A.id_indicador
                                    ) AS alertas,
                                array(
                                    SELECT '{\"mes\":\"'||mes||'/'||anio||'\",\"valor\":\"'||ROUND(AVG(calificacion)::numeric,2)||'\"}'
                                        FROM datos_evaluacion_calidad_num AA 
                                        WHERE AA.id_indicador = A.id_indicador
                                            AND calificacion != 'NaN'
                                            AND (anio < $anio OR (anio = $anio AND mes::integer <= $mes ) )
                                        GROUP BY anio, mes, id_indicador
                                        ORDER BY anio, mes
                                        LIMIT 10
                                    ) AS historial
                    FROM
                    (SELECT id_indicador, ROUND(AVG(calificacion)::numeric,2) AS calificacion
                        FROM  datos_evaluacion_calidad_num
                        WHERE anio = $anio
                            AND (mes = '$mes' OR mes = '0$mes')
                        GROUP BY id_indicador
                    ) AS A
                    INNER JOIN indicador B ON (A.id_indicador = B.id)
                    ORDER BY calificacion
                    ";
        return $em->getConnection()->executeQuery($sql)->fetchAll();        
    }
    
    /**
     * 
     * @param type $periodo
     * @return type
     * Recupera el detalle de un indicador
     */
    public function getDetalleIndicador($periodo, $id_indicador) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);        

        $datos = array();
        $calificaciones = array();
        $sql = "SELECT B.id, D.nombre as nombre_establecimieto, D.nombre_corto AS ESTABLECIMIENTO, B.codigo AS codigo_criterio, COALESCE(C.descripcion, B.descripcion) AS AREA,
                    A.calificacion
                    FROM
                    (SELECT establecimiento, codigo_criterio, ROUND(AVG(calificacion)::numeric,2) AS calificacion
                        FROM  datos_evaluacion_calidad_num
                        WHERE anio = $anio
                            AND (mes = '$mes' OR mes = '0$mes')
                            AND id_indicador = $id_indicador
                        GROUP BY anio, mes, establecimiento, codigo_criterio
                    ) AS A
                    INNER JOIN variable_captura B ON (A.codigo_criterio = B.codigo)
                    LEFT JOIN area_variable_captura C ON (B.area_id = C.id)
                    INNER JOIN costos.estructura D ON (A.establecimiento = D.codigo)
                    ORDER BY calificacion
                    ";
        $actual=  $em->getConnection()->executeQuery($sql)->fetchAll();
        
        $sql = "SELECT B.id, D.nombre as nombre_establecimieto, D.nombre_corto AS ESTABLECIMIENTO, B.codigo AS codigo_criterio, COALESCE(C.descripcion, B.descripcion) AS AREA,
                    A.calificacion, A.periodo
                    FROM
                    (SELECT mes||'/'||anio as periodo, establecimiento, codigo_criterio, ROUND(AVG(calificacion)::numeric,2) AS calificacion
                        FROM  datos_evaluacion_calidad_num
                        WHERE id_indicador = $id_indicador
                        GROUP BY anio, mes, establecimiento, codigo_criterio
                    ) AS A
                    INNER JOIN variable_captura B ON (A.codigo_criterio = B.codigo)
                    LEFT JOIN area_variable_captura C ON (B.area_id = C.id)
                    INNER JOIN costos.estructura D ON (A.establecimiento = D.codigo)
                    ORDER BY calificacion
                    ";
        $historico = $em->getConnection()->executeQuery($sql)->fetchAll();
        $resp['actual'] = $actual;
        $resp['historico'] = $historico;
        
        return $resp;
        /*        
        $sql = "SELECT B.id, B.codigo AS codigo_criterio, COALESCE(C.descripcion, B.descripcion) AS descripcion_criterio,
                    A.calificacion
                    FROM
                    (SELECT codigo_criterio, ROUND(AVG(calificacion)::numeric,2) AS calificacion
                        FROM  datos_evaluacion_calidad_num
                        WHERE anio = $anio
                            AND (mes = '$mes' OR mes = '0$mes')
                            AND id_indicador = $id_indicador
                        GROUP BY codigo_criterio
                    ) AS A
                    INNER JOIN variable_captura B ON (A.codigo_criterio = B.codigo)
                    LEFT JOIN area_variable_captura C ON (B.area_id = C.id)
                    ORDER BY calificacion
                    ";
        $areas = $em->getConnection()->executeQuery($sql)->fetchAll();
        
        $sql = "SELECT B.nombre, A.calificacion
                    FROM
                    (SELECT establecimiento, ROUND(AVG(calificacion)::numeric,2) AS calificacion
                        FROM  datos_evaluacion_calidad_num
                        WHERE anio = $anio
                            AND (mes = '$mes' OR mes = '0$mes')
                            AND id_indicador = $id_indicador
                        GROUP BY establecimiento
                    ) AS A
                    INNER JOIN costos.estructura B ON (A.establecimiento = B.codigo)
                    ORDER BY calificacion
                    ";        
        $establecimientos = $em->getConnection()->executeQuery($sql)->fetchAll();
        
        $resp['areas'] = $areas;
        $resp['establecimientos'] = $establecimientos;
        
        return $resp;*/
    }
    
    protected function prepararDatosEvaluacionNumerica($periodo) {
        $em = $this->getEntityManager();
        
        $this->crearTablaIndNumericos();
        $formularios = $em->getRepository("GridFormBundle:Formulario")->findBy(array('areaCosteo'=>'calidad', 'formaEvaluacion'=>'rango_colores'));
        
        foreach ($formularios as $Frm) {
            $this->getDatosEvaluacionNumerica($Frm, $periodo);
        }
    }
    
    protected function getDatosEvaluacionNumerica(Formulario $Frm, $periodo){
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $campos = $this->getListaCampos($Frm);
        if ($campos == '') {return;}
        $periodo_lectura = '';        
        $frmId = $Frm->getId();
        
        if ($Frm->getPeriodoLecturaDatos() == 'mensual' and $mes != null){
            $periodo_lectura = " AND (A.datos->'mes')::integer = '$mes' ";
            $where_ = '';
        } else {            
            $where_ = " AND (substring(nombre_pivote::text from '..$') = '$mes' OR substring(nombre_pivote::text from '..$') = '0$mes') "; 
            
        }
        
        $sql = "DROP TABLE IF EXISTS datos_indicadores_tmp";
        $em->getConnection()->executeQuery($sql);
        
        //Sacar los datos sobre los que se harán los cálculos
        $datos = "SELECT indicador_id, id_formulario,
                            codigo_variable AS codigo_criterio, 
                            establecimiento, area_id,
                            AVG(dato::numeric) AS calificacion
                            INTO TEMP datos_indicadores_tmp
                            FROM 
                                (SELECT $campos, A.datos->'establecimiento' as establecimiento, 
                                    A.id_formulario, C.indicador_id, B.area_id
                                    FROM almacen_datos.repositorio A
                                        INNER JOIN variable_captura B ON (A.datos->'codigo_variable' = B.codigo) 
                                        INNER JOIN indicador_variablecaptura C ON (B.id = C.variablecaptura_id)
                                    WHERE A.datos->'es_poblacion' = 'false'
                                        AND A.id_formulario = '$frmId'                        
                                        AND A.datos->'es_separador' = 'false'
                                        AND A.datos->'anio' = '$anio'
                                        $periodo_lectura
                                ) AS A
                            WHERE A.dato is not null
                                AND dato != ''
                                AND dato != 'NaN'
                                AND dato != 'Infinity'
                                $where_
                            GROUP BY indicador_id, id_formulario,
                                    codigo_variable, establecimiento, area_id                    
                "
            ;
        
        $cons = $em->getConnection()->executeQuery($datos);
        if ($cons->rowCount() > 0){
            $sql = "DELETE FROM datos_evaluacion_calidad_num
                        WHERE (id_formulario, id_indicador, codigo_criterio, 
                                anio, mes, establecimiento, id_area_criterio)
                                IN 
                                (SELECT id_formulario::integer, indicador_id::integer, 
                                        codigo_criterio::text,
                                        $anio::integer, '$mes'::text, establecimiento::text, area_id::integer
                                    FROM datos_indicadores_tmp
                                )";
            $em->getConnection()->executeQuery($sql);
            
            $sql = "INSERT INTO datos_evaluacion_calidad_num(id_formulario, id_indicador, codigo_criterio, 
                                anio, mes, establecimiento, id_area_criterio, calificacion)
                                
                                SELECT id_formulario, indicador_id, codigo_criterio,
                                    $anio, '$mes', establecimiento, area_id, calificacion
                                    FROM datos_indicadores_tmp
                                ";
            $em->getConnection()->executeQuery($sql);
            
        }
    }
    protected function getDatosEvaluacionListaChequeo(Formulario $Frm, $periodo, $datosIndicador){

        $porcentajeAprobacion = $datosIndicador['porcentaje_aceptacion']; 
        $formaEvaluacion = $datosIndicador['forma_evaluacion'] ;
        list($anio, $mes) = explode('_', $periodo);
        $em = $this->getEntityManager();
        
        $this->datos($Frm, $periodo, $datosIndicador);
        
        if ($formaEvaluacion == 'cumplimiento_porcentaje_aceptacion'){
          $evaluacion = " ROUND((COALESCE(B.expedientes_cumplimiento, 0)::numeric / A.total_expedientes::numeric * 100),2)  AS calificacion ";
        }elseif ($formaEvaluacion == 'cumplimiento_criterios') {
          $evaluacion = " ROUND((A.criterios_cumplidos::numeric / (A.criterios_aplicables::numeric) * 100),2) AS calificacion ";
        }
        
        $this->prepararTabla($datosIndicador['codigo_estandar'], $datosIndicador['codigo'], $anio, $mes);
        
        
        $sql = "DROP TABLE IF EXISTS auxiliar_tmp";
        $em->getConnection()->executeQuery($sql);
        $sql = "SELECT F.*, 
                CE.nombre AS nombre_establecimiento, COALESCE(CE.nombre_corto, CE.nombre) AS nombre_corto,
                $anio AS anio, '$mes' AS mes, '$datosIndicador[codigo_estandar]' AS codigo_estandar, '$datosIndicador[descripcion_estandar]' AS descripcion_estandar,
                 '$datosIndicador[codigo]' AS codigo_indicador, '$datosIndicador[descripcion]' AS descripcion_indicador,
                 '$datosIndicador[tipo_evaluacion]' AS tipo_evaluacion, '$datosIndicador[meta]' AS meta, '$datosIndicador[posicion]' AS posicion   
                INTO auxiliar_tmp
                FROM 
                (   SELECT A.establecimiento, A.total_expedientes, COALESCE(B.expedientes_cumplimiento, 0) AS expedientes_cumplimiento,
                    A.criterios_aplicables, A.criterios_cumplidos, A.criterios_no_cumplidos, 
                    $evaluacion
                    FROM 
                        (
                        SELECT establecimiento, COUNT(expediente) AS total_expedientes, SUM(cumplimiento) AS criterios_cumplidos,
                            SUM(no_cumplimiento) AS criterios_no_cumplidos, SUM(aplicable) AS criterios_aplicables
                            FROM  evaluacion_expediente_tmp    
                            GROUP BY establecimiento
                        ) AS A
                    LEFT JOIN 
                        (   
                            SELECT  establecimiento, COUNT(expediente) AS expedientes_cumplimiento
                            FROM evaluacion_expediente_tmp
                            WHERE porc_cumplimiento >= $porcentajeAprobacion
                            GROUP BY establecimiento
                        ) AS B 
                        ON (A.establecimiento = B.establecimiento)                    
                    INNER JOIN ctl_establecimiento_simmow ES ON (A.establecimiento = ES.id::text)
                ) AS F
                    INNER JOIN costos.estructura CE ON (F.establecimiento = CE.codigo)
                ORDER BY calificacion DESC
         ";
        
        $em->getConnection()->executeQuery($sql);
        
        //Borrar los datos si estos ya existen
        $sql = "DELETE FROM datos_evaluacion_calidad
                 WHERE (codigo_estandar, codigo_indicador, anio, mes,
                         establecimiento)
                         IN 
                         (SELECT codigo_estandar::text, codigo_indicador::text, anio::integer, mes::text,
                                    establecimiento::text
                            FROM auxiliar_tmp
                        )";
        $em->getConnection()->executeQuery($sql);
        
        // Insertar los nuevos datos
        $sql = "INSERT INTO datos_evaluacion_calidad(codigo_estandar, codigo_indicador, anio, mes,
                         descripcion_indicador, calificacion, nombre_establecimiento, nombre_corto, 
                        establecimiento, total_expedientes, expedientes_cumplimiento, criterios_aplicables, 
                        criterios_cumplidos, criterios_no_cumplidos) 
                    SELECT codigo_estandar, codigo_indicador, anio, mes,
                            descripcion_indicador, calificacion, nombre_establecimiento, nombre_corto, 
                            establecimiento, total_expedientes, expedientes_cumplimiento, criterios_aplicables, 
                            criterios_cumplidos, criterios_no_cumplidos
                    FROM auxiliar_tmp
                " ;
        $em->getConnection()->executeQuery($sql);
        
        $sql = "SELECT * FROM auxiliar_tmp";
        return $em->getConnection()->executeQuery($sql)->fetchAll();
        
        
    }
    
    private function prepararTabla($codigo_estandar, $codigo_indicador, $anio, $mes) {
        $em = $this->getEntityManager();
        
        //$this->crearTabla();
        
        //Borrar los datos antiguos de la evaluación y actualizarla con los nuevos
        $sql = "DELETE FROM datos_evaluacion_calidad
                 WHERE codigo_estandar = '$codigo_estandar'
                    AND codigo_indicador = '$codigo_indicador'
                    AND anio = $anio
                    AND mes = '$mes'
                    ";
        $em->getConnection()->executeQuery($sql);
    }
    
    public function crearTabla() {
        $em = $this->getEntityManager();
        
        //Verificar si existe la tabla, sino crearla
        $sql = "CREATE TABLE IF NOT EXISTS datos_evaluacion_calidad(
                    codigo_estandar     varchar(40),
                    codigo_indicador    varchar(60),
                    anio                integer,
                    mes                 varchar(5),
                    descripcion_indicador text,
                    calificacion        float,
                    nombre_establecimiento text,
                    nombre_corto        varchar(30),
                    establecimiento     varchar (20),
                    total_expedientes   integer,
                    expedientes_cumplimiento integer,
                    criterios_aplicables   integer,
                    criterios_cumplidos     integer,
                    criterios_no_cumplidos  integer,
                    id_area_criterio      integer
                 )";
        
        $em->getConnection()->executeQuery($sql);
        
        //Verificar si existe la tabla de límites de aceptación, sino crearla
        $sql = "CREATE TABLE IF NOT EXISTS limites_aceptacion_calidad(
                    codigo varchar(100),
                    valor float,
                    PRIMARY KEY (codigo)
                 )";        
        $em->getConnection()->executeQuery($sql);
        
        $sql = "INSERT INTO limites_aceptacion_calidad (codigo, valor)
                 select key, value::numeric from json_each_text('{".'"nivel_aprobacion_indicador_calidad":80,
                                            "nivel_aprobacion_estandar_calidad":80,
                                            "nivel_aprobacion_establecimiento_calidad":80}'."')
                    WHERE key NOT IN (SELECT codigo FROM limites_aceptacion_calidad)";
        $em->getConnection()->executeQuery($sql);
        
        //Verificar si existe la tabla de límites de aceptación, sino crearla
        $sql = "CREATE TABLE IF NOT EXISTS rangos_alertas_generales(
                    limite_inferior double precision,
                    limite_superior double precision,
                    color           character varying(50),
                    PRIMARY KEY (limite_inferior, limite_superior, color)
                 )";        
        $em->getConnection()->executeQuery($sql);
        
        $sql = "INSERT INTO rangos_alertas_generales (limite_inferior, limite_superior, color)
                    SELECT * FROM ( SELECT unnest(ARRAY[0,60,80]::float[]) AS limite_inferior, 
                            unnest(ARRAY[59.9, 79.9, 100]::float[]) AS limite_superior, 
                            unnest(ARRAY['#D73925', '#ffa500', '#008D4C']::varchar[]) AS color) AS A
                    WHERE (limite_inferior, limite_superior, color) 
                        NOT IN 
                        (SELECT limite_inferior, limite_superior, color FROM rangos_alertas_generales)";
        $em->getConnection()->executeQuery($sql);
        
    }
    
    private function crearTablaIndNumericos() {
        $em = $this->getEntityManager();
        
        //Verificar si existe la tabla, sino crearla
        $sql = "CREATE TABLE IF NOT EXISTS datos_evaluacion_calidad_num(
                    id_formulario       integer,
                    id_indicador    integer,
                    codigo_criterio     varchar(200),
                    anio                integer,
                    mes                 varchar(5),
                    calificacion        float,
                    establecimiento     varchar (20),
                    id_area_criterio      integer
                 )";
        
        $em->getConnection()->executeQuery($sql);
    }
    
    private function datos(Formulario $Frm, $periodo, $datosIndicador) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $campos = $this->getListaCampos($Frm);
        if ($campos == ''){
            //Es un formulario padre, verificar que formulario hijo contiene los
            //criterios del indicador
            $sql = "SELECT A.formulario_id
                        FROM variable_captura A
                            INNER JOIN indicador_variablecaptura B ON (A.id = B.variablecaptura_id)
                        WHERE B.indicador_id = $datosIndicador[indicador_id]
                        GROUP BY A.formulario_id
                            ";
            $form = $em->getConnection()->executeQuery($sql)->fetch();
            $Frm = $em->getRepository("GridFormBundle:Formulario")->find($form['formulario_id']);
            $campos = $this->getListaCampos($Frm);
        }
        //$alcance = $datosIndicador['alcance_evaluacion'] ;
        
        $periodo_lectura = '';
        if ($Frm->getPeriodoLecturaDatos() == 'mensual' and $mes != null){
            $periodo_lectura = " AND (A.datos->'mes')::integer = '$mes' ";
        }
               
        $sql = "DROP TABLE IF EXISTS datos_tmp";
        $em->getConnection()->executeQuery($sql);
        
        $sql = "            
                SELECT $campos, A.datos->'establecimiento' as establecimiento, AC.indicador_id,
                    A.datos->'es_poblacion' AS es_poblacion, A.datos->'codigo_tipo_control' AS tipo_control, 
                    A.datos->'es_separador' AS es_separador, A.datos->'posicion' AS posicion
                INTO TEMP datos_tmp 
                FROM almacen_datos.repositorio A
                    INNER JOIN variable_captura AB ON (A.datos->'codigo_variable' = AB.codigo) 
                    INNER JOIN indicador_variablecaptura AC ON (AB.id = AC.variablecaptura_id)
                 WHERE A.datos->'es_separador' != 'true'
                    AND A.datos->'anio' = '$anio'        
                    AND AC.indicador_id = '$datosIndicador[indicador_id]' 
                    $periodo_lectura                
                 ";
       
        $em->getConnection()->executeQuery($sql);
        
        $this->borrarVacios($mes);
        
        $sql = "DROP TABLE IF EXISTS evaluacion_expediente_tmp";
        $em->getConnection()->executeQuery($sql);
        
        // Resultado de la evaluación de cada expediente
        $sql = "SELECT establecimiento, pivote AS expediente, SUM(cumplimiento) as cumplimiento, 
                    SUM(no_cumplimiento) AS no_cumplimiento,
                    SUM(cumplimiento) + SUM(no_cumplimiento) AS aplicable,
                    ROUND( (SUM(cumplimiento)::numeric / ( SUM(cumplimiento)::numeric + SUM(no_cumplimiento)::numeric ) * 100),0) AS porc_cumplimiento 
                INTO TEMP evaluacion_expediente_tmp
                FROM (
                    SELECT establecimiento, substring(nombre_pivote, '[0-9]{1,}') as pivote, 
                        CASE WHEN dato = 'true' OR dato = '1' THEN 1 ELSE 0 END AS cumplimiento, 
                        CASE WHEN tipo_control = 'checkbox' AND dato != 'true' AND dato != '1' THEN 1 
                            WHEN tipo_control = 'checkbox_3_states' AND dato = 'false' OR dato = '0' THEN 1
                            ELSE 0 
                        END AS no_cumplimiento 
                        FROM datos_tmp 
                        WHERE es_poblacion='false'
                            AND es_separador != 'true'
                    ) AS A 
                GROUP BY establecimiento, pivote 
                HAVING (SUM(cumplimiento)::numeric + SUM(no_cumplimiento)::numeric) > 0
                ORDER BY pivote::numeric";
        $em->getConnection()->executeQuery($sql);
    }
    
    public function borrarVacios($mes) {
        $em = $this->getEntityManager();
        
        //Verificar si tiene la variable num_exp para obtener qué expedientes se ingresaron
        $sql = "SELECT codigo_variable FROM datos_tmp WHERE es_poblacion = 'true'";
        $cons = $em->getConnection()->executeQuery($sql);

        if ($cons->rowCount() > 0){
            //Quitar las columnas para las que no se ingresó número de expediente
            $sql = "DELETE FROM datos_tmp 
                    WHERE (establecimiento::text, nombre_pivote::text)
                        NOT IN 
                        (SELECT establecimiento::text, nombre_pivote::text 
                            FROM datos_tmp 
                            WHERE es_poblacion::text = 'true' 
                                AND dato is not null 
                                AND trim(dato::text) != ''
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
                    WHERE (establecimiento, nombre_pivote)
                        NOT IN 
                        (SELECT establecimiento, nombre_pivote 
                            FROM datos_tmp 
                            WHERE 
                                (nombre_pivote::text = 'mes_check_$mes' OR nombre_pivote::text = 'mes_check_0$mes')
                                AND dato is not null 
                                AND dato::text != ''
                        )";
            $em->getConnection()->executeQuery($sql);
        }
    }
    
    public function getEvaluacionesComplementarias($codigo_establecimiento = null, $raw = false) {
        $em = $this->getEntityManager();
        $resp = array();
        
        $cond = '';
        if ($codigo_establecimiento != null){
            $cond = " AND D.codigo = '$codigo_establecimiento' ";
        }

        //Obtener valores de evaluaciones externas, extraer la medición 
        //del último año ingresado para cada evaluación
        $sql = "SELECT D.codigo as establecimiento, D.nombre_corto, D.id AS id_estructura, 
                        C.descripcion AS categoria, B.descripcion AS tipo_evaluacion, 
                       A.anio, A.valor, B.unidad_medida
                    FROM evaluacion_externa A
                    INNER JOIN evaluacion_externa_tipo B ON (A.tipoevaluacion_id = B.id)
                    INNER JOIN evaluacion_categoria C ON (B.categoriaevaluacion_id = C.id)
                    INNER JOIN costos.estructura D ON (A.establecimiento_id = D.id)
                    WHERE (D.id, tipoevaluacion_id, anio) 
                        IN 
                        (SELECT establecimiento_id, tipoevaluacion_id, MAX(anio) AS anio 
                            FROM evaluacion_externa 
                            GROUP BY establecimiento_id, tipoevaluacion_id
                        )
                        $cond
                    ORDER BY C.id, B.id, A.anio, A.valor";       
        if ($raw){
            return $em->getConnection()->executeQuery($sql)->fetchAll();
        }
        else{ 
            foreach ($em->getConnection()->executeQuery($sql)->fetchAll() as $f){
                $resp[$f['establecimiento']][] = $f;
            }
            return $resp;
        }
    }
    
    public function getEvaluacionesComplementariasNacional() {
        $em = $this->getEntityManager();
  
        //Obtener valores de evaluaciones externas, extraer la medición 
        //del último año ingresado para cada evaluación
        $sql = "SELECT B.descripcion AS tipo_evaluacion, 
                       A.anio, AVG(A.valor) AS valor, B.unidad_medida
                    FROM evaluacion_externa A
                    INNER JOIN evaluacion_externa_tipo B ON (A.tipoevaluacion_id = B.id)
                    INNER JOIN evaluacion_categoria C ON (B.categoriaevaluacion_id = C.id)
                    WHERE (tipoevaluacion_id, anio) 
                        IN 
                        (SELECT tipoevaluacion_id, MAX(anio) AS anio 
                            FROM evaluacion_externa 
                            GROUP BY tipoevaluacion_id
                        )
                    GROUP BY B.descripcion, A.anio, B.unidad_medida
                    ORDER BY B.descripcion, A.anio, B.unidad_medida";       
        
        return $em->getConnection()->executeQuery($sql)->fetchAll();
    }
    
    public function getEvaluacionEstablecimiento($periodo) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $sqlEvalEstandar = $this->getSQLEvaluacionEstandar();
        $sqlEvalEstablecimiento = $this->getSQLEvaluacionEstablecimiento($sqlEvalEstandar);
        
        
        $sql_lc = "SELECT A.establecimiento, nombre_corto, nombre_establecimiento, ROUND(B.calificacion::numeric,2) as calificacion
                    INTO  TEMP esta_lc_tmp
                    FROM ($sqlEvalEstablecimiento) AS B
                    INNER JOIN datos_evaluacion_calidad A ON (B.establecimiento = A.establecimiento
                                                                AND B.anio = A.anio
                                                                AND B.mes = A.mes)
                    WHERE A.anio=$anio AND A.mes = '$mes'
                     ";

        $em->getConnection()->executeQuery($sql_lc);
        
        $sql_nm = "SELECT A.establecimiento, B.nombre_corto, 
                    B.nombre AS nombre_establecimiento
                    INTO  TEMP esta_num_tmp
                    FROM datos_evaluacion_calidad_num A
                    INNER JOIN costos.estructura B ON (A.establecimiento = B.codigo)
                    WHERE anio=$anio AND mes = '$mes'
                        AND A.establecimiento 
                            NOT IN
                        (SELECT establecimiento FROM esta_lc_tmp) 
                    GROUP BY A.establecimiento, B.nombre_corto,  B.nombre";
        
        $em->getConnection()->executeQuery($sql_nm);
        
        $sql = "SELECT 'LISTA_CHECK' AS tipo, establecimiento, nombre_corto, 
                        nombre_establecimiento, calificacion, 
                        (SELECT color FROM rangos_alertas_generales WHERE calificacion >= limite_inferior AND calificacion <= limite_superior LIMIT 1) AS color
                    FROM esta_lc_tmp
                UNION    
                SELECT 'NUMERIC' AS tipo, establecimiento, nombre_corto,
                        nombre_establecimiento, 0 as calificacion, '#0EAED8' AS color 
                    FROM esta_num_tmp
                ORDER BY tipo";
        return $em->getConnection()->executeQuery($sql)->fetchAll();
    }
    
    public function getSQLEvaluacionEstandar(){
        $em = $this->getEntityManager();
        
        $sql = "SELECT valor FROM limites_aceptacion_calidad WHERE codigo = 'nivel_aprobacion_indicador_calidad' ";        
        $nivel = $em->getConnection()->executeQuery($sql)->fetch();
        
        //Si el nivel de aprovación es 0 se hará un promedio normal
        $eval = ($nivel['valor'] == 0 ) ? 
                " ROUND(avg(calificacion)::numeric,2) " :
                " COUNT(CASE WHEN calificacion >= $nivel[valor] THEN 1 END)::numeric / COUNT(calificacion)::numeric * 100 ";
        return "SELECT anio, mes, establecimiento, codigo_estandar, 
                            $eval AS calificacion
                    FROM datos_evaluacion_calidad
                    GROUP BY anio, mes, establecimiento, codigo_estandar
                     ";
    }
    
    public function getSQLEvaluacionEstablecimiento($sqlEvalEstandar) {
        $em = $this->getEntityManager();
        
        $sql = "SELECT valor FROM limites_aceptacion_calidad WHERE codigo = 'nivel_aprobacion_indicador_calidad' ";        
        $nivel = $em->getConnection()->executeQuery($sql)->fetch();
        //Si el nivel de aprovación es 0 se hará un promedio normal
        $eval = ($nivel['valor'] == 0 ) ? 
                " ROUND(avg(calificacion)::numeric,2) " :
                " COUNT(CASE WHEN calificacion >= $nivel[valor] THEN 1 END)::numeric / COUNT(calificacion)::numeric * 100 ";
        return  " SELECT anio, mes, establecimiento, 
                        $eval AS calificacion
                        FROM ($sqlEvalEstandar) AS CST
                        GROUP BY anio, mes, establecimiento
                 ";
    }
    
    public function getEvaluaciones($establecimiento, $periodo, $nivelAprobacionIndicador = 0) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        $sqlEvalEstandar = $this->getSQLEvaluacionEstandar();
        $sql = "SELECT A.codigo, A.descripcion, A.periodo_lectura_datos, A.meta, 
                        A.forma_evaluacion, ROUND(B.calificacion::numeric,2) as calificacion,
                        (SELECT color FROM rangos_alertas_generales WHERE B.calificacion >= limite_inferior AND B.calificacion <= limite_superior LIMIT 1) AS color
                    FROM costos.formulario A
                        INNER JOIN ($sqlEvalEstandar
                            ) AS B ON (A.codigo = B.codigo_estandar)
                    WHERE B.anio=$anio 
                        AND B.mes = '$mes'
                        AND B.establecimiento = '$establecimiento'
                    ORDER BY A.posicion, A.codigo
                     ";
        return $em->getConnection()->executeQuery($sql)->fetchAll();
          
    }
    
    public function getHistorialEstablecimiento($establecimiento, $periodo) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $sqlEvalEstandar = $this->getSQLEvaluacionEstandar();
        $sqlEvalEstablecimiento = $this->getSQLEvaluacionEstablecimiento($sqlEvalEstandar);
     
        $sql = "SELECT A.anio, A.mes::integer, ROUND(A.calificacion::numeric,2) AS calificacion 
                    FROM ($sqlEvalEstablecimiento) AS A 
                    WHERE A.establecimiento = '$establecimiento'
                       AND (A.anio < $anio OR (A.anio = $anio AND A.mes::integer <= $mes ) )                    
                    ORDER BY A.anio, A.mes::integer
                    LIMIT 20
                     ";
        return $em->getConnection()->executeQuery($sql)->fetchAll();
          
    }
    
    public function getDatosCalidad() {
        $em = $this->getEntityManager();
        
        $sql = "SELECT * 
                    FROM datos_evaluacion_calidad                     
                     ";
        return $em->getConnection()->executeQuery($sql)->fetchAll();
          
    }
    
    public function getListaCampos(Formulario $Frm, $array = true, $mes = null) {
        $campos = '';
        $soloMes = ($Frm->getPeriodoLecturaDatos() == 'anual' and $mes!=null and $mes!='') ? true : false;
        foreach ($Frm->getCampos() as $c){
            $piv = $c->getOrigenPivote();
            $codigoCampo = $c->getSignificadoCampo()->getCodigo();
            if ($piv != ''){
                $piv_ = json_decode($piv);
                //La parte de datos
                $campos .= ($array) ? "unnest(array[" : '';
                foreach($piv_ as $p){
                    $alias = ($array) ? '' : ' AS "'.$p->descripcion.'" ';
                    if ($soloMes){
                        if ($p->id == $mes)
                            $campos .= " datos->'".$codigoCampo."_".$p->id."'". $alias.", ";
                    } else {
                        $campos .= " datos->'".$codigoCampo."_".$p->id."'". $alias.", ";
                    }
                }
                $campos = ($array) ? trim($campos, ', ') : $campos;
                $campos .= ($array) ? "]) AS dato, " : '';
                
                //La parte del nombre del campo
                $campos .= ($array) ? "unnest(array[" : '';                
                foreach($piv_ as $p){
                    if ($soloMes){
                        if ($p->id == $mes)
                            $campos .= "'$codigoCampo"."_".$p->id."', ";
                    } else {
                        $campos .= "'$codigoCampo"."_".$p->id."', ";
                    }                    
                }
                $campos = ($array) ? trim($campos, ', ') : $campos;
                $campos .= ($array) ? "]) AS nombre_pivote, " : '';
            } else {
                $campos .= " datos->'$codigoCampo' AS $codigoCampo, ";
            }
        }
        return trim($campos, ', ');
    }
    
    public function getResumenEvaluacionIndicadores($establecimiento, $periodo, $cod_formulario){
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $sql = "SELECT A.codigo, A.descripcion, B.total_expedientes, B.expedientes_cumplimiento, 
                        B.criterios_aplicables, B.criterios_cumplidos, B.criterios_no_cumplidos,
                        A.forma_evaluacion, ROUND(B.calificacion::numeric,2) AS calificacion,
                        (SELECT color FROM rangos_alertas_generales WHERE B.calificacion >= limite_inferior AND B.calificacion <= limite_superior LIMIT 1) AS color
                    FROM indicador A
                        INNER JOIN (SELECT codigo_indicador, SUM(total_expedientes) AS total_expedientes,
                            avg(calificacion) AS calificacion, SUM(expedientes_cumplimiento) AS expedientes_cumplimiento,
                            SUM(criterios_aplicables) AS criterios_aplicables, SUM(criterios_cumplidos) AS criterios_cumplidos,
                            SUM(criterios_no_cumplidos) AS criterios_no_cumplidos
                            FROM datos_evaluacion_calidad 
                            WHERE anio=$anio 
                                AND mes = '$mes'
                                AND establecimiento = '$establecimiento'
                                AND codigo_estandar = '$cod_formulario'
                            GROUP BY codigo_indicador
                            ) AS B ON (A.codigo = B.codigo_indicador)
                    ORDER BY A.codigo
                     ";
        return $em->getConnection()->executeQuery($sql)->fetchAll();
    }
    
    public function getEvaluacionesNOListaChequeo($establecimiento, $periodo){
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $sql = "SELECT descripcion AS descripcion_estandar, periodo_lectura_datos, 
                    id as estandar_id, codigo,
                    forma_evaluacion AS tipo_evaluacion, nombre AS nombre_evaluacion, 
                    meta, periodo_lectura_datos, posicion
                    FROM costos.formulario
                    WHERE id 
                        IN 
                        (SELECT COALESCE(B.id_formulario_sup, B.id)                   
                            FROM almacen_datos.repositorio A
                                INNER JOIN costos.formulario B ON (A.id_formulario = B.id)
                            WHERE area_costeo = 'calidad'
                                AND A.datos->'establecimiento' = '$establecimiento'
                                AND A.datos->'anio' = '$anio'
                                AND A.datos->'es_separador' != 'true'
                                AND B.forma_evaluacion != 'lista_chequeo'
                        )
                ORDER BY posicion    
                ";

        return $em->getConnection()->executeQuery($sql)->fetchAll();
    }
}