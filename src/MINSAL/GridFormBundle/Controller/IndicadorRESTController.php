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
     * @Get("/rest-service/calidad/indicadores/{periodo}/{tipo}", options={"expose"=true})
     * @Rest\View
     */
    public function getIndicadoresCalidadEvaluadosAction($periodo, $tipo) {
        
        $response = new Response();
        $em = $this->getDoctrine()->getManager();

        if ($tipo == 2)
            $data = $em->getRepository('GridFormBundle:Indicador')->getIndicadoresEvaluadosNumericos($periodo);
        else
            $data = $em->getRepository('GridFormBundle:Indicador')->getIndicadoresEvaluadosListaChequeo($periodo);
        
        $resp = (count($data) == 0)? array(): $data;
        
        $response->setContent(json_encode($resp));
        return $response;
    }
}
