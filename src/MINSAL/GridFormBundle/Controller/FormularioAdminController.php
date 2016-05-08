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
                        array('formulario' => 'ASC', 'periodo'=>'ASC'));

            foreach ($aux as $p ) { 
                if ($p->getFormulario()->getAreaCosteo() == 'almacen_datos' or $p->getFormulario()->getAreaCosteo() == 'calidad')
                    $periodosEstructura [] = $p;
            }            
            
            //Buscar los permisos para el formulario asignado por grupo de usuarios
            //Verificar que el usuario tiene una unidad principal para ingreso de datos
            if ($this->getUser()->getEstablecimientoPrincipal() != null) {
                foreach($this->getUser()->getGroups() as $g){
                    $aux_ = $em->getRepository("GridFormBundle:PeriodoIngresoGrupoUsuarios")
                    ->findBy(array('grupoUsuario' => $g , 'formulario'=>$Frm), 
                            array('formulario' => 'ASC', 'periodo' => 'ASC'));
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
                
        //Agrupar los periodos por unidad
        $periodos_aux = array();
        foreach($periodos as $p){
            $periodos_aux[$p['unidad']->getCodigo()]['unidad'] = $p['unidad'];
            $periodos_aux[$p['unidad']->getCodigo()]['datos'][] = $p;
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
            'editable' => $editable,
            'mostrar_resumen' => $mostrar_resumen,
            'cantidad_formularios' => 0,
            'pk' => $pk);
        
        if ($periodo != '-1'){
            //$formularios = $Frm->getGrupoFormularios();
            $formularios = $em->getRepository('GridFormBundle:Formulario')->findBy(array('formularioSup'=>$Frm), array('codigo'=>'ASC'));
            if ($formularios == null or count($formularios) == 0){
                $formularios[] = $Frm;
            }
            $parametrosPlantilla['Frm'] = $Frm;
            $parametrosPlantilla['llave_primaria'] = $pk;
            $parametrosPlantilla['cantidad_formularios'] = count($formularios);
            $parametrosPlantilla['Formularios'] = $this->ajustarFormulas($formularios);
            $parametrosPlantilla['meses_activos'] = $meses[$periodoSeleccionado->getPeriodo()->getAnio()];
            foreach ($formularios as $frm){
                $parametrosPlantilla['origenes'][$frm->getId()] = $this->getOrigenes($frm, $parametros);
                $parametrosPlantilla['pivotes'][$frm->getId()] = $this->getPivotes($frm, $parametros);
                $parametrosPlantilla['tipos_datos_por_filas'][$frm->getId()] = $em->getRepository('GridFormBundle:Formulario')->tipoDatoPorFila($frm);
            }
            
                        
        }
        return $this->render($plantilla, $parametrosPlantilla);
    }
    /**
     * 
     * @param Formulario[] $formularios
     * @return Formulario[] $frm_ajustados
     * 
     * Esta funciones cambia las fórmulas, que fueron ingresados con el código
     * de la variable, al correspondiente número de fila en el grid. En el grid
     * la fórmula se ejecuta por número de fila que ocupan.
     */
    protected function ajustarFormulas($formularios){
        $em = $this->getDoctrine()->getManager();
        $frm_ajustados = array();
        foreach ($formularios as $f){
            $var_ = $em->getRepository("GridFormBundle:VariableCaptura")->findBy(array('formulario'=>$f), array('posicion'=>'ASC'));
            $i = 0;
            $formula = $f->getCalculoFilas();
            foreach($var_ as $v){
                $formula = str_replace($v->getCodigo(), 'F'.$i++, $formula);
            }
            $f->setCalculoFilas($formula);
            $frm_ajustados[] = $f;
        }
        return $frm_ajustados;
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
