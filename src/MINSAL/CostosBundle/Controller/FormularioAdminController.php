<?php

namespace MINSAL\CostosBundle\Controller;

use MINSAL\GridFormBundle\Controller\FormularioAdminController AS FormularioAdminControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class FormularioAdminController extends FormularioAdminControllerBase
{
    public function rrhhValorPagadoAction(Request $request)
    {        
        return $this->mostrarPlantilla($request, 'rrhhValorPagado', 'nit', '_rrhhValorPagado_', false, 'GridFormBundle:Formulario:parametros.html.twig');
    }
    
    public function rrhhDistribucionHoraAction(Request $request)
    {        
        return $this->mostrarPlantilla($request, 'rrhhDistribucionHora', 'nit', '_rrhhDistribucionHora_', false, 'GridFormBundle:Formulario:parametros.html.twig');
    }
    
    public function rrhhCostosAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        
        $estructura = $em->getRepository("CostosBundle:Estructura")->findBy(array(), array('codigo' => 'ASC'));
        
        $parametros = $this->getParametros($request);
        $Frm = $em->getRepository('GridFormBundle:Formulario')->findBy(array('codigo'=>'rrhhValorPagado'));
        $Frm = array_shift($Frm);
        
        $periodo = (is_null($request->get('periodo_estructura')) ) ? '-1': $request->get('periodo_estructura');
        $numero_carga = (is_null($request->get('periodo_estructura')) ) ? '1': '2';
        
        if ($periodo != '-1'){
            $periodos_sel = explode('_', $periodo);
            $tipo_periodo = $periodos_sel[0];
            if ($periodos_sel[0]=='pu'){
                $periodoSeleccionado = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")->find($periodos_sel[1]);
            }else {
                $periodoSeleccionado = $em->getRepository("GridFormBundle:PeriodoIngresoGrupoUsuarios")->find($periodos_sel[1]);
            }
        } else {
            $periodoSeleccionado = null;
        }
        $periodosEstructura = array();
        
        $periodosEstructura = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")
                ->findBy(array('usuario' => $this->getUser(), 'formulario'=>$Frm), 
                        array('periodo' => 'ASC', 'unidad'=>'ASC'));
        
        foreach ($periodosEstructura as $p){
            $llave = $llave = $p->getPeriodo()->getAnio().$p->getUnidad()->getId().$p->getFormulario()->getId();
            $periodos[$llave] = array('id'=>'pu_'.$p->getId(),
                                                'periodo_anio'=>$p->getPeriodo()->getAnio(),
                                                'periodo_mes'=>$p->getPeriodo()->getMes(),
                                                'unidad' => $p->getUnidad(),
                                                'formulario' => $p->getFormulario()
                                            );
            if ($Frm == $p->getFormulario())
                $meses[$p->getPeriodo()->getAnio()][] = $p->getPeriodo()->getMes();
        }
                
        //Agrupar los periodos por formulario
        $periodos_aux = array();
        foreach($periodos as $p){
            $periodos_aux[$p['formulario']->getCodigo()]['nombre'] = $p['formulario']->getNombre();
            $periodos_aux[$p['formulario']->getCodigo()]['datos'][] = $p;
        }
        
        $campos = array('nit'=>10, 
            'partida'=>20,
            'subpartida' =>30,
            'nombre_empleado' => 40,
            'tipo_empleado' => 50,
            'especialidad' => 60,
            'forma_contrato' => 70,
            'distribucion_horas' => 1000
            );
        $campos_calculados = array('isss_patronal'=>'ISSS patronal', 
            'fondo_proteccion_patronal'=>'AFP / IPFA patronal',
            'costo_con_aporte_y_aguinaldo' => 'Costo con aporte y aguinaldo',
            'costo_hora_aporte_aguinaldo' => 'Costo por hora con aportes, permisos CG y aguinaldo',
            'costo_hora_no_trab_CG' => 'Costo horas no trab. CG',
            'costo_hora_no_trab_SG' => 'Costo horas no trab. SG',
            'salario_descuentos_permisos' => 'Salario con desc. y permisos',
            'costo_hora_descuentos_permisos' => 'Costo hora con desc. y permisos'
            );
                
        $Frm2 = $em->getRepository('GridFormBundle:Formulario')->findBy(array('codigo'=>'rrhhDistribucionHora'));
        $Frm2 = array_shift($Frm2);
        $Frm_aux = new \MINSAL\GridFormBundle\Entity\Formulario();
        
        $Frm_aux->setAreaCosteo('rrhh');
        $Frm_aux->setColumnasFijas(4);
        $Frm_aux->setIdentificador($Frm->getId());
        $campos_aux = array();
        
        $formato = new \MINSAL\GridFormBundle\Entity\Formato();
        $alineacion = new \MINSAL\GridFormBundle\Entity\Alineacion();
        $tipo_dato = new \MINSAL\GridFormBundle\Entity\TipoDato();
        $tipo_control = new \MINSAL\GridFormBundle\Entity\TipoControl();
        
        $alineacion->setCodigo('right');
        $formato->setFormato('c2');
        $tipo_dato->setCodigo('float');
        $tipo_control->setCodigo('text');
                        
        foreach (array($Frm, $Frm2) as $F) {
            foreach ($F->getCampos() as $c){
                if (array_key_exists($c->getSignificadoCampo()->getCodigo(), $campos)){
                    $c->setPosicion($campos[$c->getSignificadoCampo()->getCodigo()]);
                    if ($c->getSignificadoCampo()->getCodigo() == 'distribucion_horas'){
                        $c->getSignificadoCampo()->setCodigo('_costo');                        
                        $c->setFormato($formato);
                        $c->setEsCalculado(true);
                        $c->setAlineacion($alineacion);
                    }
                    $c->setEsEditable(false);
                    $campos_aux[$c->getPosicion()] = $c;
                }
            }
        }
        ksort($campos_aux);
        $distribucion = $campos_aux[1000];
        unset($campos_aux[1000]);
        foreach($campos_aux as $c){
            $Frm_aux->addCampo($c);
        }
               
        foreach($campos_calculados as $k=>$v){
            $significado_c = new \MINSAL\IndicadoresBundle\Entity\SignificadoCampo();
            
            $campo_c = new \MINSAL\GridFormBundle\Entity\Campo();

            $significado_c->setCodigo($k);
            $significado_c->setDescripcion($v);
            
            $campo_c->setSignificadoCampo($significado_c);
            $campo_c->setTipoDato($tipo_dato);
            $campo_c->setTipoControl($tipo_control);
            $campo_c->setEsEditable(false);
            $campo_c->setEsCalculado(true);
            $campo_c->setFormato($formato);
            $campo_c->setAlineacion($alineacion);
            
            $Frm_aux->addCampo($campo_c);
        }
        
        $Frm_aux->addCampo($distribucion);
        
        $origenes = $this->getOrigenes($Frm, $parametros) + $this->getOrigenes($Frm2, $parametros);
        $pivotes = $this->getPivotes($Frm, $parametros) + $this->getPivotes($Frm2, $parametros);
        
        return $this->render('GridFormBundle:Formulario:parametros.html.twig', array('Frm' => $Frm_aux, 
            'origenes' => $origenes,
            'pivotes' => $pivotes,
            'url' => 'get_grid_data',
            'url_save' => 'set_grid_data',
            'estructura' => $estructura,
            'parametros' => $parametros,
            'periodos' => $periodos_aux,
            'periodoSeleccionado' => $periodoSeleccionado,
            'numero_carga' => $numero_carga,
            'titulo' => '_rrhhCostos_',
            'mostrar_resumen' => true,
            'editable' => false,
            'pk' => 'nit'));
    }
    
    public function gaAfAction(Request $request)
    {        
        return $this->mostrarPlantilla($request, 'gaAf', 'codigo_af', '_gaAf_', false, 'GridFormBundle:Formulario:parametros.html.twig');
    }
    
    public function gaCompromisosFinancierosAction(Request $request)
    {        
        return $this->mostrarPlantilla($request, 'gaCompromisosFinancieros', 'codigo_contrato', '_gaCompromisosFinancieros_', false, 'GridFormBundle:Formulario:parametros.html.twig');
    }     
    
    public function gaVariablesAction(Request $request)
    {        
        return $this->mostrarPlantilla($request, 'gaVariables', 'dependencia', '_gaVariables_', false, 'GridFormBundle:Formulario:parametros.html.twig');
    }        
    
    public function gaDistribucionAction(Request $request)
    {        
        return $this->mostrarPlantilla($request, 'gaDistribucion', 'dependencia', '_gaDistribucion_', false, 'GridFormBundle:Formulario:parametros.html.twig');
    }
    
    public function gaCostosAction(Request $request)
    {        
        //Request $request, $codigoFrm, $pk, $titulo, $mostrar_resumen, $plantilla, $editable = true
        //return $this->mostrarPlantilla($request, 'gaDistribucion', 'dependencia', '_ga_costos_', true, 'parametrosDependenciaGACosteo', false);
        $em = $this->getDoctrine()->getManager();
        
        $estructura = $em->getRepository("CostosBundle:Estructura")->findBy(array(), array('codigo' => 'ASC'));
        
        //$Frm = $em->getRepository('GridFormBundle:Formulario')->findOneBy(array('codigo'=>'gaDistribucion'));
        $Frm = $this->get('costos.repository.formulario')->findOneBy(array('codigo'=>'gaDistribucion'));
        $Frm->setAreaCosteo('ga_costos');
        
        $parametros = $this->getParametros($request);
        $datos = array();
        $dependencias = array();
        $grupos = array();
        $datos_costos = array();
        $totales = array();
        $i=0;
        if ($parametros['anio_mes'] != null and $parametros['establecimiento'] != null){       
            $datos = $this->get('costos.repository.formulario')->getDatosCostosGA($Frm, $request);
            
            foreach($datos as $f){
                $f['total_gasto'] = ($f['total_gasto'] == null)? 0: $f['total_gasto'];
                $dependencias[$f['dependencia']] = $f['nombre_dependencia'];
                $grupos[$f['criterio_distribucion']]['nombre'] = $f['nombre_criterio_distribucion'];
                $grupos[$f['criterio_distribucion']]['compromisos'][$f['codigo_compromiso']] = $f['nombre_compromiso'];
                $datos_costos[$f['dependencia']][$f['codigo_compromiso']] = $f['total_gasto'];
                
                if (array_key_exists('d'.$f['dependencia'], $totales))
                    $totales['d'.$f['dependencia']] += $f['total_gasto'];
                else
                    $totales = array_merge ($totales, array('d'.$f['dependencia']=> $f['total_gasto']));
                
                if (array_key_exists($f['codigo_compromiso'], $totales))
                    $totales[$f['codigo_compromiso']] += $f['total_gasto'];
                else
                    $totales = array_merge ($totales, array($f['codigo_compromiso']=> $f['total_gasto']));
                
                if (array_key_exists('general', $totales))
                    $totales['general'] += $f['total_gasto'];
                else
                    $totales = array_merge ($totales, array('general'=> $f['total_gasto']));
                //$totales[$f['codigo_compromiso']] += $f['total_gasto'];
                //$totales['general'] += $f['total_gasto'];
            }        
        }
        return $this->render('CostosBundle:Formulario:parametrosDependenciaGACosteo.html.twig', array(
            'estructura' => $estructura,
            'parametros' => $parametros,
            'dependencias' => $dependencias,
            'grupos' => $grupos,
            'datos_costos' => $datos_costos,
            'totales' => $totales,
            'titulo' => '_gaCostos_',
            ));
    }    
}