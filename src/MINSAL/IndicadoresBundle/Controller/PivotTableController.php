<?php

namespace MINSAL\IndicadoresBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;


class PivotTableController extends Controller {

    
    /**
     * @Route("/pivotable/guardar_estado/", name="pivotable_guardar_estado", options={"expose"=true})
     */
    public function guardarEstadoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $response = new Response();
        
        $tipoElemento = $request->get('tipoElemento');
        $idElemento = $request->get('id');
        //json_encode sobre json_decode, quita los saltos de línea que trae configuración
        $configuracion = json_encode(json_decode($request->get('configuracion')));
        
        $usuario = $this->get('security.token_storage')->getToken()->getUser();
        
        $em->getRepository("IndicadoresBundle:FichaTecnica")->setConfPivoTable($tipoElemento, $idElemento, $usuario->getId(), $configuracion);
        
        return $response;
    }

    /**
     * @Route("/pivotable/obtener_estado/", name="pivotable_obtener_estado", options={"expose"=true})
     */
    public function obtenerEstadoAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $response = new Response();
        
        $tipoElemento = $request->get('tipoElemento');
        $idElemento = $request->get('id');
        $usuario = $this->get('security.token_storage')->getToken()->getUser();
        
        $conf = $em->getRepository("IndicadoresBundle:FichaTecnica")->getConfPivoTable($tipoElemento, $idElemento, $usuario->getId());
        //$conf = str_replace('\\\\', '', $conf['configuracion']);
        $response->setContent($conf['configuracion']);
        return $response;
    }
    
    /**
     * @Route("/pivotable/datos/{id}", name="get_datos_evaluacion_calidad", options={"expose"=true})
     */
    public function getDatosEvaluacionCalidadAction($id, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $response = new Response();
        
        $datos = $em->getRepository("GridFormBundle:Indicador")->getDatosCalidad($id);
        
        $response->setContent(json_encode($datos));
        return $response;
    }

}