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
        
        $params_string = $this->getParameterString( $parametros, $periodoIngreso->getId(), $tipo_periodo, $user);
        $this->origenes = $this->getOrigenes($Frm->getOrigenDatos());
        $this->campo = 'id_origen_dato';
        
        $this->cargarDatos($Frm);
        
        $tabla =  ($this->area == 'almacen_datos') ? 'almacen_datos.repositorio' : 'costos.fila_origen_dato_'.strtolower($this->area);
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
        
        if ($this->area == 'almacen_datos'){
            $this->origenes = array($Frm->getId());
            $this->campo = 'id_formulario';
            $dependencia1 = ''; $dependencia2 = ''; $dependencia3='';
            if (array_key_exists('dependencia', $this->parametros)){
                $dependencia1 = " 'dependencia', ";
                $dependencia2 = "'". $this->parametros['dependencia'] . "' , ";
                $dependencia3 = " datos->'dependencia', ";
            }
            
            //Si es mensual agregar el mes a la consulta
            $mes_txt = ""; $mes_val = ""; $mes_txt2 = ""; $mes_condicion = "";
            if ($Frm->getPeriodoLecturaDatos() == 'mensual'){
                $mes_txt = " 'mes', ";
                $mes_val = " '" . $this->parametros['mes'] . "', ";
                $mes_txt2 = " datos->'mes',";
                $mes_condicion = " AND datos->'mes' = '" . $this->parametros['mes'] . "' ";
            }
            //Cargar las variables que no están en el año elegido
            $sql = "INSERT INTO almacen_datos.repositorio (id_formulario, datos)
                    (SELECT ".$Frm->getId()." AS id_formulario, 
                            hstore(
                                ARRAY['codigo_variable', 'anio', $mes_txt 'establecimiento', $dependencia1 'descripcion_variable',
                                        'codigo_categoria_variable', 'descripcion_categoria_variable', 'es_poblacion', 'posicion', 
                                        'es_separador', 'nivel_indentacion', 'regla_validacion'], 
                                ARRAY[A.codigo , '".$this->parametros['anio']."', $mes_val '".$this->parametros['establecimiento']."', $dependencia2 A.descripcion,
                                    B.codigo, B.descripcion,  COALESCE(A.es_poblacion::varchar,''), COALESCE(A.posicion::varchar,'0'), 
                                    COALESCE(A.es_separador::varchar,''), COALESCE(A.nivel_indentacion::varchar,'0'), COALESCE(A.regla_validacion::varchar,'')]
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
                            ||('\"descripcion_variable\"=>'||'\"'||COALESCE(A.descripcion,'')||'\"')::hstore
                            ||('\"regla_validacion\"=>'||'\"'||COALESCE(A.regla_validacion::varchar,'')||'\"')::hstore
                            ||('\"codigo_tipo_control\"=>'||'\"'||COALESCE(C.codigo::varchar,'')||'\"')::hstore
                        FROM variable_captura A
                            INNER JOIN categoria_variable_captura B ON (A.id_categoria_captura = B.id)
                            LEFT JOIN costos.tipo_control C ON (A.id_tipo_control = C.id)
                        WHERE almacen_datos.repositorio.datos->'codigo_variable' = A.codigo";
            $em->getConnection()->executeQuery($sql);
        }        
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

    private function getParameterString($parametros, $periodoIngreso = null, $tipo_periodo = null, $user = null ) {
        $params_string = '';
        $em = $this->getEntityManager();
        if ($tipo_periodo == null or $tipo_periodo == 'pu'){
            $periodoIngreso = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")->find($periodoIngreso);
        } elseif($tipo_periodo == 'pg'){
            $periodoIngreso = $em->getRepository("GridFormBundle:PeriodoIngresoGrupoUsuarios")->find($periodoIngreso);
        }        
        if ($periodoIngreso !=  null ){
            if ($periodoIngreso->getFormulario()->getPeriodoLecturaDatos() == 'mensual')
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
        if ($area != 'ga_variables' and $area != 'ga_compromisosFinancieros' and $area != 'ga_distribucion' and $area != 'almacen_datos'){
            $origenes = $this->getOrigenes($Frm->getOrigenDatos());
            $campo = 'id_origen_dato';
            $tabla = 'costos.fila_origen_dato_'.strtolower($area);
        } else {
            $origenes = array($Frm->getId());
            $campo = 'id_formulario';
            $tabla =  ($area == 'almacen_datos') ? 'almacen_datos.repositorio' : 'costos.fila_origen_dato_ga';
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
}