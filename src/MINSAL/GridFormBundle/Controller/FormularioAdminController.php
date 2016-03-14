<?php

namespace MINSAL\GridFormBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class FormularioAdminController extends Controller
{           
    public function mostrarPlantilla(Request $request, $codigoFrm, $pk, $titulo, $mostrar_resumen, $plantilla, $editable = true) {
        $em = $this->getDoctrine()->getManager();
        $meses = Array();
        
        $periodo = (is_null($request->get('periodo_estructura')) ) ? '-1': $request->get('periodo_estructura');
        $numero_carga = (is_null($request->get('periodo_estructura')) ) ? '1': '2';
        $tipo_periodo = null;
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

        $parametros = $this->getParametros2($periodoSeleccionado, $tipo_periodo);       
        
        $parametros['tipo_periodo'] = $tipo_periodo;
        $periodos= array();
        $cantFrm = 1;
        // Si es el código de formulario de captura de datos, 
        // pueden haber varios formularios para el usuario
        $periodosEstructura = array();        
        if ($codigoFrm == 'captura_variables'){
            $Frm = null;
            if ($periodo != '-1'){
                //Si ya se eligió un periodo ya se puede determinar el formulario, seleccionado
                $Frm = $periodoSeleccionado->getFormulario();
            }
            //Buscar todos los formularios del areaCosteo almacen_datos asignados al usuario
            $aux = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")
                ->findBy(array('usuario' => $this->getUser()), 
                        array('periodo' => 'ASC', 'unidad'=>'ASC'));

            foreach ($aux as $p ) { 
                if ($p->getFormulario()->getAreaCosteo() == 'almacen_datos')
                    $periodosEstructura [] = $p;
            }            
            
            //Buscar los permisos para el formulario asignado por grupo de usuarios
            //Verificar que el usuario tiene una unidad principal para ingreso de datos
            if ($this->getUser()->getEstablecimientoPrincipal() != null) {
                foreach($this->getUser()->getGroups() as $g){
                    $aux_ = $em->getRepository("GridFormBundle:PeriodoIngresoGrupoUsuarios")
                    ->findBy(array('grupoUsuario' => $g , 'formulario'=>$Frm), 
                            array('periodo' => 'ASC'));
                    foreach($aux_ as $p){
                        $llave = $p->getPeriodo()->getAnio().$this->getUser()->getEstablecimientoPrincipal()->getId().$p->getFormulario()->getId();
                        $periodos[$llave] = array('id'=>'pg_'.$p->getId(),
                                                'periodo_anio'=>$p->getPeriodo()->getAnio(),
                                                'periodo_mes'=>$p->getPeriodo()->getMes(),
                                                'unidad' => $this->getUser()->getEstablecimientoPrincipal(),
                                                'formulario' => $p->getFormulario()
                                            );
                        $meses[$p->getPeriodo()->getAnio()][] = $p->getPeriodo()->getMes();
                    }
                }
            }            
        }
        else{ 
            $Frm = $em->getRepository('GridFormBundle:Formulario')->findOneBy(array('codigo'=>$codigoFrm));
            $periodosEstructura = $em->getRepository("GridFormBundle:PeriodoIngresoDatosFormulario")
                ->findBy(array('usuario' => $this->getUser(), 'formulario'=>$Frm), 
                        array('periodo' => 'ASC', 'unidad'=>'ASC'));
        }    

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
        
        $parametrosPlantilla = array(
            'url' => 'get_grid_data',
            'url_save' => 'set_grid_data',
            'parametros' => $parametros,
            'periodos'=>$periodos_aux,
            'tipo_periodo'=>$tipo_periodo,
            'periodoSeleccionado' => $periodoSeleccionado,
            'titulo' => $titulo,
            'numero_carga' => $numero_carga,
            'cantFrm' => $cantFrm,
            'editable' => $editable,
            'mostrar_resumen' => $mostrar_resumen,
            'pk' => $pk);
        
        if ($periodo != '-1'){
            $parametrosPlantilla['Frm'] = $Frm;
            $parametrosPlantilla['origenes'] = $this->getOrigenes($Frm, $parametros);
            $parametrosPlantilla['pivotes'] = $this->getPivotes($Frm, $parametros);
            $parametrosPlantilla['meses_activos'] = $meses[$periodoSeleccionado->getPeriodo()->getAnio()]; 
            $parametrosPlantilla['tipos_datos_por_filas'] = $em->getRepository('GridFormBundle:Formulario')->tipoDatoPorFila($Frm);
                        
        }
        return $this->render($plantilla, $parametrosPlantilla);
    }
        
    public function almacenDatosAction(Request $request)
    {        
        return $this->mostrarPlantilla($request, 'captura_variables', 'codigo_variable', '_captura_datos_', false, 'GridFormBundle:Formulario:parametros.html.twig');
    }    
    
    protected function getOrigenes($Frm, $parametros) {
        $em = $this->getDoctrine()->getManager();
        $origenes = array();
        foreach($Frm->getCampos() as $c){
            if ($c->getOrigen() or $c->getSignificadoCampo()->getCatalogo() != ''){
                $origenes[$c->getSignificadoCampo()->getCodigo()] = $em->getRepository('GridFormBundle:Campo')->getOrigenCampo($c, $parametros);
            }            
        }
        return $origenes;
    }
    
    protected function getPivotes($Frm, $parametros) {
        $em = $this->getDoctrine()->getManager();
        $pivotes = array();
        foreach($Frm->getCampos() as $c){
            if ($c->getOrigenPivote()){
                $pivotes[$c->getSignificadoCampo()->getCodigo()] = $em->getRepository('GridFormBundle:Campo')->getOrigenPivote($c, $parametros);
            }
        }
        return $pivotes;
    }
    
    protected function getParametros2($periodoIngreso, $tipo_periodo){
        $parametros = array();
        if ($tipo_periodo == 'pu'){
            $unidad = $periodoIngreso->getUnidad();
        } elseif ($tipo_periodo == 'pg'){
            $unidad = $this->getUser()->getEstablecimientoPrincipal();
        }
        
        if ($periodoIngreso !=  null ){
            if ($periodoIngreso->getFormulario()->getPeriodoLecturaDatos() == 'mensual')
                $parametros['mes'] = $periodoIngreso->getPeriodo()->getMes();
            $parametros['anio'] = $periodoIngreso->getPeriodo()->getAnio();
            if ($unidad->getNivel() == 1 ) {
                $parametros['establecimiento'] = $unidad->getCodigo();
            } elseif ($unidad->getNivel() == 2 ) {
                $parametros['establecimiento'] = $unidad->getParent()->getCodigo();
                $parametros['dependencia'] = $unidad->getId();
            } elseif ($unidad->getNivel() == 3 ) {
                $parametros['establecimiento'] = $unidad->getParent()->getParent()->getCodigo();
                $parametros['dependencia'] = $unidad->getCodigo();
            }
            $parametros['periodo_estructura'] =  $tipo_periodo.'_'.$periodoIngreso->getId();
        } else {
            $parametros =  array('anio_mes'=>null, 
                'anio'=>null, 
                'establecimiento'=>null,
                'dependencia'=>null,
                'periodo_estructura' => null
                );
        }
        return $parametros;
    }
    
    protected function getParametros($r){
        return array('anio_mes'=>$r->get('anio_mes'),
            'anio'=>$r->get('anio'),
            'establecimiento'=>$r->get('establecimiento'),
            'dependencia'=>$r->get('dependencia'),
            'periodo_estructura' => $r->get('periodo_estructura')
            );
    }
}
