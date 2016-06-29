<?php

namespace MINSAL\GridFormBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\Request;

class TableroCalidadRESTController extends Controller {

    /**
     * Obtener los datos del formulario
     * @Get("/rest-service/tablero-calidad/evaluaciones", options={"expose"=true})
     * @Rest\View
     */
    public function getPeriodosEvaluacionAction() {
        $response = new Response();

        $resp = array();

        $em = $this->getDoctrine()->getManager();
        
        $meses = array(1=>'Ene.',
                2=>'Feb.',
                3=>'Mar.',
                4=>'Abr.',
                5=>'May.',
                6=>'Jun.',
                7=>'Jul.',
                8=>'Ago.',
                9=>'Sep.',
                10=>'Oct.',
                11=>'Nov.',
                12=>'Dic.'
        );
        $data = $em->getRepository('GridFormBundle:Formulario')->getPeriodosEvaluacion();
        $data_ = array();
        foreach ($data as $f){
            $f['etiqueta'] = $meses[$f['mes']].'/'.$f['anio'];
            $data_[]= $f;
        }
        if (count($data_) == 0) {
            $response->setContent('{"estado" : "error", "msj": "' . $this->get('translator')->trans('_no_datos_') . '"}');
        } else {
            $response->setContent(json_encode($data_));
        }

        return $response;
        //}
    }
    
    /**
     * Obtener los datos del formulario
     * @Get("/rest-service/tablero-calidad/establecimientos/{periodo}", options={"expose"=true})
     * @Rest\View
     */
    public function getEstablecimientosEvaluadosAction($periodo) {
        
        $response = new Response();
        $em = $this->getDoctrine()->getManager();

        $data = $em->getRepository('GridFormBundle:Formulario')->getEstablecimientosEvaluados($periodo);
        
        $resp = (count($data) == 0)? array(): $data;
        
        $response->setContent(json_encode($resp));

        return $response;
    }
    
    /**
     * Obtener los datos del formulario
     * @Get("/rest-service/tablero-calidad/evaluaciones/{establecimiento}/{periodo}", options={"expose"=true})
     * @Rest\View
     */
    public function getEvaluacionesAction($establecimiento, $periodo) {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();

        $data = $em->getRepository('GridFormBundle:Formulario')->getEvaluaciones($establecimiento, $periodo);
        $resp = (count($data) == 0)? array(): $data;
        
        $response->setContent(json_encode($resp));

        return $response;
    }
    
    /**
     * Obtener los datos del formulario
     * @Get("/rest-service/tablero-calidad/evaluaciones/{establecimiento}/{periodo}/{formulario}", options={"expose"=true})
     * @Rest\View
     */
    public function getCriteriosAction($establecimiento, $periodo, $formulario) {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        //$resp = array();
        
        $datos_resumen = $em->getRepository('GridFormBundle:Formulario')->getCriterios($establecimiento, $periodo, $formulario);
        $datos = $datos_resumen['datos'];
        $data_= '';
        
        $data =  array();
        foreach ($datos as $d) {            
            $data[$d['codigo']]['descripcion'] = $d['descripcion'];
            $data[$d['codigo']]['forma_evaluacion'] = $d['forma_evaluacion'];
            $data[$d['codigo']]['criterios'][] = json_decode('{'.str_replace('=>', ':', $d['datos']).'}', true);            
        }
        $data_ = array();
        foreach ($data as $d){
            $data_[] = array('descripcion'=>$d['descripcion'], 'forma_evaluacion'=>$d['forma_evaluacion'], 
                            'criterios'=>$d['criterios'],
                            'resumen' => $datos_resumen['resumen']
                        );
        }
        $resp = json_encode($data_); 
        
        $response->setContent($resp);

        return $response;
    }
    
    
    /**
     * Obtener los datos del formulario
     * @Get("/rest-service/tablero-calidad/historial/{establecimiento}/{periodo}", options={"expose"=true})
     * @Rest\View
     */
    public function getHistorialEstablecimientoAction($establecimiento, $periodo) {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        
        $data = $em->getRepository('GridFormBundle:Formulario')->getHistorialEstablecimiento($establecimiento, $periodo);
        $resp = (count($data) == 0)? array(): $data;
        
        $response->setContent(json_encode($resp));
        
        return $response;
    }
}
