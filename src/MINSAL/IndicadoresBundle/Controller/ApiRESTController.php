<?php

namespace MINSAL\IndicadoresBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use MINSAL\IndicadoresBundle\Entity\FichaTecnica;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use Predis;

class ApiRESTController extends Controller {
    /**
     * Obtener los datos de los indicadores asignados a una agencia
     * @param string $cod_agencia
     * @Get("/api/{cod_agencia}/indicadores/data", options={"expose"=true})
     * @Rest\View
     */
    public function getIndicadoresAction($cod_agencia) {
        $em = $this->getDoctrine()->getManager();
        
        $agencia = $em->getRepository("IndicadoresBundle:Agencia")->findOneBy(array('codigo'=>$cod_agencia));
        
        $response = new Response();        
        
        $respuesta = array('state'=>'ok');
        $datos = array();
        $fichaRepository = $em->getRepository('IndicadoresBundle:FichaTecnica');
        
        foreach ($agencia->getIndicadores() as $ind){
            try {
                $fichaRepository->crearIndicador($ind);
                $datosIndicador = $fichaRepository->getDatosIndicador($ind);
                $datos[] = array('indicador_id'=>$ind->getId(), 'nombre'=>$ind->getNombre(), 'datos'=>$datosIndicador);
            } catch (Exception $exc) {
                $respuesta = array('state'=>'fail', 'message'=>$exc->getTraceAsString());
                $response->setContent(json_encode($respuesta));
                return $response;
            }
        }
        $respuesta['datos'] = $datos;
        $response->setContent(json_encode($respuesta));
        return $response;
    }
    
    /**
     * Obtener los datos de los indicadores asignados a una agencia
     * @param string $cod_agencia
     * @Get("/api/{cod_agencia}/fichastecnicas/listado", options={"expose"=true})
     * @Rest\View
     */
    public function getFichasListadoAction($cod_agencia) {
        $em = $this->getDoctrine()->getManager();
        
        $agencia = $em->getRepository("IndicadoresBundle:Agencia")->findOneBy(array('codigo'=>$cod_agencia));
        
        $response = new Response();        
        
        $respuesta = array('state'=>'ok');
        $datos = array();
        $fichaRepository = $em->getRepository('IndicadoresBundle:FichaTecnica');
        
        foreach ($agencia->getIndicadores() as $ind){
            try {
                $datos[] = array('id_ficha'=>$ind->getId(), 'nombre'=>$ind->getNombre());
            } catch (Exception $exc) {
                $respuesta = array('state'=>'fail', 'message'=>$exc->getTraceAsString());
                $response->setContent(json_encode($respuesta));
                return $response;
            }
        }
        $respuesta['datos'] = $datos;
        $response->setContent(json_encode($respuesta));
        return $response;
    }
    
    /**
     * Obtener los datos de los indicadores asignados a una agencia
     * @param string $cod_agencia
     * @Get("/api/{cod_agencia}/fichastecnicas", options={"expose"=true})
     * @Rest\View
     */
    public function getFichasAction($cod_agencia) {
        $em = $this->getDoctrine()->getManager();
        
        $agencia = $em->getRepository("IndicadoresBundle:Agencia")->findOneBy(array('codigo'=>$cod_agencia));
        
        $response = new Response();        
        
        $respuesta = array('state'=>'ok');
        $datos = array();
        $fichaRepository = $em->getRepository('IndicadoresBundle:FichaTecnica');
        
        foreach ($agencia->getIndicadores() as $ind){
            try {
                $clasificacionTecnica = array();
                foreach ($ind->getClasificacionTecnica() as $c){
                    $clasificacionTecnica[] = $c->getDescripcion();
                }
                $variables =  array();
                foreach($ind->getVariables() as $v){
                    $variables[] = $v->getIniciales();
                }
                $alertas =  array();
                foreach($ind->getAlertas() as $a){
                    $alertas[] = array('limite_inferior' => $a->getLimiteInferior(),
                                    'limite_superior' => $a->getLimiteSuperior(),
                                    'color' => $a->getColor()->getColor()
                                );
                }
                $datos[] = array('id_ficha'=>$ind->getId(), 
                    'nombre'=>$ind->getNombre(),
                    'interpretacion' => $ind->getTema(),
                    'concepto' => $ind->getConcepto(),
                    'unidad_medida' => $ind->getUnidadMedida(),
                    'formula'=> $ind->getFormula(),
                    'observacion'=> $ind->getObservacion(),
                    'campos' =>$ind->getCamposIndicador(),
                    'clasificacion_tecnica' => $clasificacionTecnica,
                    'meta' => $ind->getMeta(),
                    'variables' => $variables,
                    'alertas' => $alertas
                    );
            } catch (Exception $exc) {
                $respuesta = array('state'=>'fail', 'message'=>$exc->getTraceAsString());
                $response->setContent(json_encode($respuesta));
                return $response;
            }
        }
        $respuesta['datos'] = $datos;
        $response->setContent(json_encode($respuesta));
        return $response;
    }
}
