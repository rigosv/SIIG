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

        $data = $em->getRepository('GridFormBundle:Formulario')->getPeriodosEvaluacion();

        if (count($data) == 0) {
            $response->setContent('{"estado" : "error", "msj": "' . $this->get('translator')->trans('_no_datos_') . '"}');
        } else {
            $response->setContent(json_encode($data));
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
        
        $datos = $em->getRepository('GridFormBundle:Formulario')->getCriterios($establecimiento, $periodo, $formulario);
        $data_= '';
        
        /*$ultimo = array_pop($datos);
        foreach ($datos as $d){            
            $data_ .= '{'.  str_replace('=>', ':', $d['datos']). '},';
        }
        $data_ .= '{'.  str_replace('=>', ':', $ultimo['datos']). '}';
         */
        $data =  array();
        foreach ($datos as $d) {            
            $data[$d['codigo']]['descripcion'] = $d['descripcion'];
            $data[$d['codigo']]['forma_evaluacion'] = $d['forma_evaluacion'];
            $data[$d['codigo']]['criterios'][] = json_decode('{'.str_replace('=>', ':', $d['datos']).'}', true);            
        }
        $data_ = array();
        foreach ($data as $d){
            $data_[] = array('descripcion'=>$d['descripcion'], 'forma_evaluacion'=>$d['forma_evaluacion'], 'criterios'=>$d['criterios']);
        }
        $resp = json_encode($data_); 
        
        $response->setContent($resp);

        return $response;
    }
    
    
    /**
     * Obtener los datos del formulario
     * @Get("/rest-service/tablero-calidad/historial/{establecimiento}", options={"expose"=true})
     * @Rest\View
     */
    public function getHistorialEstablecimientoAction($establecimiento) {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        
        $data = $em->getRepository('GridFormBundle:Formulario')->getHistorialEstablecimiento($establecimiento);
        $resp = (count($data) == 0)? array(): $data;
        
        $response->setContent(json_encode($resp));
        
        return $response;
    }
}
