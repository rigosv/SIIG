<?php

namespace MINSAL\GridFormBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use Symfony\Component\HttpFoundation\Request;

class IndicadorRESTController extends Controller
{
    /**
     * Obtener los datos del formulario
     * @Get("/rest-service/calidad/indicadores/{periodo}", options={"expose"=true})
     * @Rest\View
     */
    public function getIndicadoresCalidadEvaluadosAction($periodo) {
        
        $response = new Response();
        $em = $this->getDoctrine()->getManager();

        $data = $em->getRepository('GridFormBundle:Indicador')->getIndicadoresEvaluados($periodo);
        
        $resp = (count($data) == 0)? array(): $data;
        
        $response->setContent(json_encode($resp));
        return $response;
    }
}
