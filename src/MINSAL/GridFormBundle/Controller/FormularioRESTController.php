<?php

namespace MINSAL\GridFormBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;

class FormularioRESTController extends Controller {

        private $meses = array(1 => 'Ene.',
            2 => 'Feb.',
            3 => 'Mar.',
            4 => 'Abr.',
            5 => 'May.',
            6 => 'Jun.',
            7 => 'Jul.',
            8 => 'Ago.',
            9 => 'Sep.',
            10 => 'Oct.',
            11 => 'Nov.',
            12 => 'Dic.'
        );
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
        //Verificar que existen las tablas necesarias
        $em->getRepository('GridFormBundle:Indicador')->crearTabla();
        
        $data_ = array();
        foreach ($data as $f) {
            $f['etiqueta'] = $this->meses[$f['mes']] . '/' . $f['anio'];
            $data_[] = $f;
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
     * @Get("/rest-service/formulario/data/{codigo}", options={"expose"=true})
     * @Rest\View
     */
    public function getDatosFormularioCapturaAction($codigo) {
        $response = new Response();

        // crea una respuesta con una cabecera ETag y Last-Modified
        // para determinar si se debe calcular el indicador u obtener de la caché
        // para el modo de desarrollo (dev) nunca tomar de caché
        //$response->setETag($fichaTec->getId().'_datos');
        //$response->setLastModified(($this->get('kernel')->getEnvironment() == 'dev') ? new \DateTime('NOW') : $fichaTec->getUltimaLectura() );

        $response->setPublic();
        // verifica que la respuesta no se ha modificado para la petición dada
        //if ($response->isNotModified($this->getRequest())) {
        // devuelve inmediatamente la respuesta 304 de la caché
        //  return $response;
        //} else {
        $resp = array();

        $em = $this->getDoctrine()->getManager();

        $data = $em->getRepository('GridFormBundle:Formulario')->getDatosCapturaDatos($codigo);

        if (count($data) == 0) {
            $response->setContent('{"estado" : "error", "msj": "' . $this->get('translator')->trans('_no_datos_') . '"}');
        } else {
            $response->setContent(json_encode($data));
        }

        return $response;
        //}
    }

}
