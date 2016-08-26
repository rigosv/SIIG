<?php

namespace MINSAL\IndicadoresBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


/**
* @Route("/reportes")
*/
class ReportesController extends Controller {

    /**
     * @Route("/matriz_seguimiento", name="matriz-seguimiento")
     */
    public function matrizSeguimientoAccion() {
        $admin_pool = $this->get('sonata.admin.pool');
        
        $em = $this->getDoctrine()->getManager();
        
        $Frm = $em->find('GridFormBundle:Formulario', 12);
        $datosFrm = $em->getRepository('GridFormBundle:Formulario')->getDatosRAW($Frm);

        $params = array('2015'=>array('01', '02', '03'), '2016'=>array('01','02','03','04','05'));
        
        $datos_ = array();
        foreach ($datosFrm as $f){
            if (array_key_exists($f['anio'], $params)){
                
                foreach ($f as $k => $sf){
                    $mesVarR = str_replace('cant_mensual_calidad_', '', $k);
                    if (in_array($mesVarR, $params[$f['anio']])){
                        $datos_['real'][$mesVarR.'/'.$f['anio']] = $sf;
                    }
                    $mesVarP = str_replace('_p', '', str_replace('cant_mensual_calidad_', '', $k));
                    if (in_array($mesVarP, $params[$f['anio']])){
                        $datos_['planificado'][$mesVarP.'/'.$f['anio']] = $sf;
                    }
                }
                if (count($datos_['real']) > 0){
                    foreach ($datos_['planificado'] as $k=>$v){
                        $datos_['estatus'][$k] = ($v > 0) ? number_format(($datos_['real'][$k] / $v) * 100,0) : null; 
                    }
                    $datosFrmFormat[$f['codigo_variable']]['datos']= $datos_;
                    $datosFrmFormat[$f['codigo_variable']]['descripcion'] = $f['descripcion_variable'];
                    $datosFrmFormat[$f['codigo_variable']]['categoria'] = $f['descripcion_categoria_variable'];
                }
            }
        }

        return $this->render('IndicadoresBundle:Reportes:matrizSeguimiento.html.twig', 
                                array(
                                    'admin_pool' => $admin_pool,
                                    'datosFrm' => $datosFrmFormat,
                                    'parms' => $params
                                ));
    }
}
