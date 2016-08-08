<?php

namespace MINSAL\GridFormBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use MINSAL\GridFormBundle\Entity\Formulario;

class GridController extends Controller
{
    /**
     * @Route("/grid/{id}/{periodo_ingreso}", name="get_grid_data", options={"expose"=true})
     * @Template()
     */
    public function getGridDataAction(Formulario $Frm, $periodo_ingreso, Request $request)
    {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        
        $tipo_periodo = null;
        if (strpos($periodo_ingreso, '_') !== false){
            $periodos_sel = explode('_', $periodo_ingreso);
            $tipo_periodo = $periodos_sel[0];
            if ($periodos_sel[0]=='pu'){
                $periodoEstructura = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")->find($periodos_sel[1]);
            }else {
                $periodoEstructura = $em->getRepository("GridFormBundle:PeriodoIngresoGrupoUsuarios")->find($periodos_sel[1]);
            }
        } else{
            $periodoEstructura = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")->find($periodo_ingreso);
        }
                
        if (!$periodoEstructura) {
            //$response->setContent('{"estado" : "ok", "data": []}');
            $response->setContent('{"estado" : "error", "msj": "' . $this->get('translator')->trans('_parametros_no_establecidos_') . '"}');
        } else{
            $user = $this->getUser();
            if ($Frm->getAreaCosteo() == 'almacen_datos' or $Frm->getAreaCosteo() == 'calidad')
                $data = $em->getRepository('GridFormBundle:Formulario')->getDatos($Frm, $periodoEstructura->getId(),$tipo_periodo , $request, $user);
            else 
                $data = $this->get('costos.repository.formulario')->getDatos($Frm, $periodoEstructura->getId(),$tipo_periodo , $request, $user);
            if (count($data) > 0){
                $data_ = '';
                $ultimo = array_pop($data);
                foreach ($data as $f){
                    $data_ .= '{'.  str_replace('=>', ':', $f['datos']). '},';
                }
                $data_ .= '{'.  str_replace('=>', ':', $ultimo['datos']). '}';

                $response->setContent('{"estado" : "ok", "data": ['. $data_. ']}');
            }
        }
        return $response;
    }
    
    /**
     * @Route("/grid/save/{id}/{periodo_ingreso}", name="set_grid_data", options={"expose"=true})
     * @Template()
     */
    public function setGridDataAction(Formulario $Frm, $periodo_ingreso, Request $request)
    {
        $data_ = '';
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        
        $tipo_periodo = null;
        if (strpos($periodo_ingreso, '_') !== false){
            $periodos_sel = explode('_', $periodo_ingreso);
            $tipo_periodo = $periodos_sel[0];
            if ($periodos_sel[0]=='pu'){
                $periodoEstructura = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")->find($periodos_sel[1]);
            }else {
                $periodoEstructura = $em->getRepository("GridFormBundle:PeriodoIngresoGrupoUsuarios")->find($periodos_sel[1]);
            }
        } else{
            $periodoEstructura = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")->find($periodo_ingreso);
        }
        
        $datos = json_decode($request->get('fila'), true);
        
        if (array_key_exists('regla_validacion', $datos) and $datos['regla_validacion'] != ''){
            $regla = $datos['regla_validacion'];
            
            foreach ($datos as $n=>$d){
                //Buscar campos pivote: cant_mensual y importe_mensual, por el momento
                if (strpos($n, 'cant_mensual_') !== false or strpos($n, 'importe_mensual_') !== false){
                    $r_ = true;
                    //aplicar la regla de validación
                    $regla_ = str_replace('value', $d, $regla);
                    $regla_msj = str_replace('value', 'valor_celda', $regla);
                    
                    eval('$r_ = ('.$regla_.');');
                    if (!$r_){
                        $response->setContent('{"estado" : "error", "msj": "' . $this->get('translator')->trans('_error_validation_') .' <h3>'.$regla_msj. '</h3>"}');
                        return $response;
                    }
                }
            }
        }
        
        //$periodoEstructura =  $em->getRepository('CostosBundle:PeriodoIngresoDatosFormulario')->find($periodo_ingreso);
        if (!$periodoEstructura) {
            $response->setContent('{"estado" : "error", "msj": "' . $this->get('translator')->trans('_parametros_no_establecidos_') . '"}');
        } else{
            //Verificar si tiene campos de control de calidad para calcular el 
            // porcentaje de ejecución
            $datos = json_decode($request->get('fila'), true);
            if (array_key_exists('origen_fila', $datos)){
                unset($datos['origen_fila']);
            }
            $data_ = json_encode($this->setPorcentajeCompletado($datos), JSON_UNESCAPED_UNICODE);
            $user = $this->getUser();
            $request->attributes->set('fila', $data_);
            $guardar = $em->getRepository('GridFormBundle:Formulario')->setDatos($Frm, $periodoEstructura->getId(), $tipo_periodo, $request, $user);
        
            if ($guardar == false){
                $response->setContent('{"estado" : "error", "msj": "' . $this->get('translator')->trans('_error_datos_no_guardados_') . '"}');
            }
            else{
                $fila = array_pop($guardar);
                $data_ = '{'.  str_replace('=>', ':', $fila['datos']). ', "local": "si"}';
                                
                $fila_array = json_decode($data_, true);
                $data_ = json_encode($this->setPorcentajeCompletado($fila_array), JSON_UNESCAPED_UNICODE);
                
                $response->setContent('{"estado" : "ok", "data": '. $data_. '}');
            }
        }
        return $response;
    }
    
    /**
     * Verificar si tiene variables de calidad para agregar el porcentaje de cumplimiento
     * Es imporante que el campo pivote generado se llame cant_mensual_calidad
     * y que se usa 01_p para la cantidad de enero planifiada
     * y 01_c para contener el % de cumplimiento
     * @param type $param
     */
    private function setPorcentajeCompletado($fila) {
        $fila_porc_completado = $fila;        
        $meses = array('01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12');

        foreach ($meses as $v){
            if (array_key_exists("cant_mensual_calidad_$v", $fila_porc_completado) 
                    and array_key_exists("cant_mensual_calidad_$v".'_p', $fila_porc_completado) 
                    and $fila_porc_completado["cant_mensual_calidad_$v".'_p'] > 0
                    ) {
                $cumplimiento = $fila_porc_completado['cant_mensual_calidad_'.$v] / $fila_porc_completado['cant_mensual_calidad_'.$v.'_p'] * 100;
                $fila_porc_completado['cant_mensual_calidad_'.$v.'_c'] = number_format ($cumplimiento, 0, '.', ',').'%';
            }
        }
        return $fila_porc_completado;
    }
    
    /**
     * @Route("/estructura/establecimiento/{codigo_establecimiento}/dependencias", name="get_dependencias", options={"expose"=true})
     */
    public function getDependencias($codigo_establecimiento, Request $request)
    {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        
        // Dejaré las consultas aquí porque aún no está definido cómo será la 
        // estructura de tablas que tendrán estos datos
        $sql = "SELECT A.codigo, A.nombre, B.nombre as nombre_unidad_organizativa
                    FROM costos.estructura A
                    INNER JOIN costos.estructura B ON (A.parent_id = B.id)                    
                    WHERE B.parent_id IN 
                        (SELECT id FROM costos.estructura
                            WHERE codigo = '$codigo_establecimiento' 
                        )
                    ORDER BY B.nombre, A.nombre
                        ";
        $dependencias = $em->getConnection()->executeQuery($sql)->fetchAll();
        
        $dependencias_html = "<OPTION VALUE=''>".$this->get('translator')->trans('_seleccione_dependencia_')."</option>";
        foreach ($dependencias as $d){
            $dependencias_html .= "<OPTION VALUE='$d[codigo]'>$d[nombre_unidad_organizativa] -- $d[nombre]</OPTION>";
        }
        $response->setContent($dependencias_html);
        
        return $response;
        
    }
    
    /**
     * @Route("/formulario/guardar_conf/{periodo_ingreso}", name="guardar_encabezado", options={"expose"=true})
     */
    public function setEncabezado($periodo_ingreso, Request $request)
    {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        
        $tipo_periodo = null;
        if (strpos($periodo_ingreso, '_') !== false){
            $periodos_sel = explode('_', $periodo_ingreso);
            $tipo_periodo = $periodos_sel[0];
            if ($periodos_sel[0]=='pu'){
                $periodoEstructura = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")->find($periodos_sel[1]);
            }else {
                $periodoEstructura = $em->getRepository("GridFormBundle:PeriodoIngresoGrupoUsuarios")->find($periodos_sel[1]);
            }
        } else{
            $periodoEstructura = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")->find($periodo_ingreso);
        }
        if ($tipo_periodo == 'pg'){            
            $unidad = $this->getUser()->getEstablecimientoPrincipal()->getCodigo();
        } else {                
            $unidad = $periodoEstructura->getUnidad()->getCodigo();
        }
        
        $datos_frm = array();
        parse_str($request->get('datos_frm'), $datos_frm);
        unset($datos_frm['fechaEvaluacion']);

        $em->getRepository("GridFormBundle:Formulario")->guardarEncabezado($periodoEstructura, $unidad, $datos_frm);
                
        return $response;
        
    }
}