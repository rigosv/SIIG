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
        
        //Recuperar los indicadores que no tienen asociado ningún criterio
        //Estos serán los que están cargados a todo el estándar
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
            $calificacionIndicador = (count($eval_) > 0) ? ($calificacion / count($eval_)) : 0;
            foreach($eval_ as $e){
                $f = $e;
                $f['calificacion_indicador'] = $calificacionIndicador;
                $eval[] = $f;
            }
            
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
        $datos = array();
        $calificaciones = array();
                
        $sql = "SELECT id, estandar_id, codigo, descripcion, forma_evaluacion, indicadorpadre_id
                    FROM indicador A                    
                    WHERE A.forma_evaluacion = 'promedio'
                        AND indicadorpadre_id is null
                    ";
        $indicadores = $em->getConnection()->executeQuery($sql)->fetchAll();
        
        foreach ($indicadores as $ind){
            //Verificar si tiene hijos
            $Indicador = $em->getRepository('GridFormBundle:Indicador')->find($ind['id']);
            
            $indHijos = $Indicador->getIndicadoresHijos();
            
            $indicadoresRecorrer = (count($indHijos) > 0) ? $indHijos : array(0=>$Indicador);
            $datosArea = array();
            $valorIndPrincipal = 0;
            foreach($indicadoresRecorrer as $f){
                $Frm = $f->getEstandar(); 
                $datosEval_ = array();
                $valor = 0;
                if ($Frm != null)
                    $datosEval_ = $this->getDatosEvaluacionNumerica($Frm, $periodo, $f);
                foreach ($datosEval_ as $r){
                    $valor = array_key_exists($this->meses[$mes], $r) ? $r[$this->meses[$mes]] : 0;
                }
                $datosArea[] = array('nombre_area' => $f->getDescripcion(),
                            'valor'=> $valor
                        );
                $valorIndPrincipal  += $valor;
            }
            $valorIndPrincipal = $valorIndPrincipal / count($indicadoresRecorrer);
            $datos[] = array ('nombre_indicador' => $Indicador->getDescripcion(),
                             'valor'=> $valorIndPrincipal,
                            'datos_area'=> $datosArea);
            
        }
        return $datos;
    }
    
    protected function getDatosEvaluacionNumerica(Formulario $Frm, $periodo, Indicador $datosIndicador){
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $campos = $this->getListaCampos($Frm, false);
        if ($campos == '') return array();
        $periodo_lectura = '';        
        
        $criterio = $datosIndicador->getCriterios();
        $codCriterio = $criterio[0]->getCodigo();
        $frmId = $Frm->getId();
        
        if ($Frm->getPeriodoLecturaDatos() == 'mensual' and $mes != null){
            $periodo_lectura = " AND (A.datos->'mes')::integer = '$mes' ";
            $mes_ = '';
            $where_ = '';
        } else {
            
            $mes_ = ' "'.$this->meses[$mes].'" AS dato, '; 
            $where_ = " WHERE dato is not null AND dato != '' "; 
            
        }
        //Sacar los datos sobre los que se harán los cálculos
            $datos = "SELECT * FROM (SELECT $mes_ *
                    FROM 
                    (SELECT $campos, A.datos->'establecimiento' as establecimiento
                        FROM almacen_datos.repositorio A
                        WHERE A.datos->'es_poblacion' = 'false'
                            AND id_formulario = '$frmId'                        
                            AND A.datos->'es_separador' = 'false'
                            AND A.datos->'anio' = '$anio'
                            AND datos->'codigo_variable' = '$codCriterio'
                            $periodo_lectura
                    ) AS AA 
                    ) AS BB
                    $where_
                    "
                ;
        return $em->getConnection()->executeQuery($datos)->fetchAll();
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
        $em->getConnection()->executeQuery($sql)->fetchAll();
        $sql = "SELECT F.*, 
                CE.nombre AS nombre_establecimiento, COALESCE(CE.nombre_corto, CE.nombre) AS nombre_corto,
                $anio AS anio, '$mes' AS mes, '$datosIndicador[codigo_estandar]' AS codigo_estandar, '$datosIndicador[descripcion_estandar]' AS descripcion_estandar,
                 '$datosIndicador[codigo]' AS codigo_indicador, '$datosIndicador[descripcion]' AS descripcion_indicador,
                 '$datosIndicador[tipo_evaluacion]' AS tipo_evaluacion, '$datosIndicador[meta]' AS meta, '$datosIndicador[posicion]' AS posicion   
                INTO TEMP auxiliar_tmp
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
        
        $this->crearTabla();
        
        //Borrar los datos antiguos de la evaluación y actualizarla con los nuevos
        $sql = "DELETE FROM datos_evaluacion_calidad
                 WHERE codigo_estandar = '$codigo_estandar'
                    AND codigo_indicador = '$codigo_indicador'
                    AND anio = $anio
                    AND mes = '$mes'
                    ";
        $em->getConnection()->executeQuery($sql);
    }
    
    private function crearTabla() {
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
                    criterios_no_cumplidos  integer
                 )";
        
        $em->getConnection()->executeQuery($sql);
    }
    
    private function datos(Formulario $Frm, $periodo, $datosIndicador) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $campos = $this->getListaCampos($Frm);
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
                INTO evaluacion_expediente_tmp
                FROM (
                    SELECT establecimiento, substring(nombre_pivote, '[0-9]{1,}') as pivote, 
                        CASE WHEN dato = 'true' THEN 1 ELSE 0 END AS cumplimiento, 
                        CASE WHEN tipo_control = 'checkbox' AND dato != 'true' THEN 1 
                            WHEN tipo_control = 'checkbox_3_states' AND dato = 'false' THEN 1
                            ELSE 0 
                        END AS no_cumplimiento 
                        FROM datos_tmp 
                        WHERE es_poblacion='false'
                            AND es_separador != 'true'
                    ) AS A 
                GROUP BY establecimiento, pivote 
                HAVING (SUM(cumplimiento)::numeric + SUM(no_cumplimiento)::numeric) > 0
                ORDER BY pivote::numeric";
        $em->getConnection()->executeQuery($sql); //->fetchAll();
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
    
    public function getEvaluacionesComplementarias($codigo_establecimiento = null) {
        $em = $this->getEntityManager();
        $resp = array();
        
        $cond = '';
        if ($codigo_establecimiento != null){
            $cond = " AND D.codigo = '$codigo_establecimiento' ";
        }

        //Obtener valores de evaluaciones externas, extraer la medición 
        //del último año ingresado para cada evaluación
        $sql = "SELECT D.codigo as establecimiento, D.id AS id_estructura, 
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
        
        foreach ($em->getConnection()->executeQuery($sql)->fetchAll() as $f){
            $resp[$f['establecimiento']][] = $f;
        }
        return $resp;
    }
    
    public function getEvaluacionEstablecimiento($periodo) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $sql = "SELECT establecimiento, nombre_corto, nombre_establecimiento, ROUND(avg(calificacion)::numeric,2) AS calificacion 
                    FROM datos_evaluacion_calidad 
                    WHERE anio=$anio AND mes = '$mes'
                    GROUP BY establecimiento, nombre_corto, nombre_establecimiento
                     ";
        return $em->getConnection()->executeQuery($sql)->fetchAll();
    }
    
    public function getEvaluaciones($establecimiento, $periodo) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $sql = "SELECT A.codigo, A.descripcion, A.periodo_lectura_datos, A.meta, 
                        A.forma_evaluacion, ROUND(B.calificacion::numeric,2) as calificacion
                    FROM costos.formulario A
                        INNER JOIN (SELECT codigo_estandar, avg(calificacion) AS calificacion 
                            FROM datos_evaluacion_calidad 
                            WHERE anio=$anio 
                                AND mes = '$mes'
                                AND establecimiento = '$establecimiento'
                            GROUP BY codigo_estandar
                            ) AS B ON (A.codigo = B.codigo_estandar)
                    ORDER BY A.posicion, A.codigo
                     ";
        return $em->getConnection()->executeQuery($sql)->fetchAll();
          
    }
    
    public function getHistorialEstablecimiento($establecimiento, $periodo) {
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $sql = "SELECT anio, mes::integer, ROUND(avg(calificacion)::numeric,2) AS calificacion 
                    FROM datos_evaluacion_calidad 
                    WHERE establecimiento = '$establecimiento'
                       AND (anio < $anio OR (anio = $anio AND mes::integer <= $mes ) )
                    GROUP BY anio, mes::integer                        
                    ORDER BY anio, mes::integer
                    LIMIT 20
                     ";
        return $em->getConnection()->executeQuery($sql)->fetchAll();
          
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
    
    public function getResumenEvaluacionIndicadores($establecimiento, $periodo, $cod_formulario){
        $em = $this->getEntityManager();
        list($anio, $mes) = explode('_', $periodo);
        
        $sql = "SELECT A.codigo, A.descripcion, B.total_expedientes, B.expedientes_cumplimiento, 
                        B.criterios_aplicables, B.criterios_cumplidos, B.criterios_no_cumplidos,
                        A.forma_evaluacion, ROUND(B.calificacion::numeric,2) AS calificacion
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
}