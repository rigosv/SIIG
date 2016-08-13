<?php

namespace MINSAL\IndicadoresBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use MINSAL\IndicadoresBundle\Entity\FichaTecnica;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use Predis;

class IndicadorRESTController extends Controller {

    /**
     * @param integer $fichaTec
     * @param string $dimension
     * @Get("/rest-service/data/{id}/{dimension}", options={"expose"=true})
     * @Rest\View
     */
    public function getIndicadorAction(FichaTecnica $fichaTec, $dimension) {
        $response = new Response();
        $redis = new Predis\Client();
        
        $filtro = $this->getRequest()->get('filtro');
        $verSql = ($this->getRequest()->get('ver_sql') == 'true') ? true : false;
        $hash = md5($filtro.$verSql);
        
        // verifica que la respuesta no se ha modificado para la petición dada
        if ($fichaTec->getUpdatedAt() != '' and $fichaTec->getUltimaLectura() != '' and $fichaTec->getUltimaLectura() < $fichaTec->getUpdatedAt()) {
            // Buscar la petición en la caché de Redis
            $respj = $redis->get('indicador_'.$fichaTec->getId().'_'.$dimension.$hash);
            if ($respj != null){
                $response->setContent($respj);
                return $response;
            }
        }
        //La respuesta de la petición no estaba en Redis, hacer el cálculo
        $resp = array();

        if ($filtro == null or $filtro == '')
            $filtros = null;
        else {

            $filtrObj = json_decode($filtro);
            foreach ($filtrObj as $f) {
                $filtros_dimensiones[] = $f->codigo;
                $filtros_valores[] = $f->valor;
            }
            $filtros = array_combine($filtros_dimensiones, $filtros_valores);
        }

        $em = $this->getDoctrine()->getManager();

        $fichaRepository = $em->getRepository('IndicadoresBundle:FichaTecnica');

        $fichaRepository->crearIndicador($fichaTec, $dimension, $filtros);
        $resp['datos'] = $fichaRepository->calcularIndicador($fichaTec, $dimension, $filtros, $verSql);        
        $respj = json_encode($resp);

        $response->setContent($respj);
        if ( is_array($resp['datos']) ){
            $redis->set('indicador_'.$fichaTec->getId().'_'.$dimension.$hash, $respj);
        }        

        return $response;
        
    }
    
    /**
     * Obtener los datos del indicador sin aplicar la fórmula ni filtros
     * @param integer $fichaTec
     * @param string $dimension
     * @Get("/rest-service/data/{id}", options={"expose"=true})
     * @Rest\View
     */
    public function getDatosIndicadorAction(FichaTecnica $fichaTec) {
        $response = new Response();        
                
        $redis = new Predis\Client();
        
        if ($fichaTec->getUpdatedAt() != '' and $fichaTec->getUltimaLectura() != '' and $fichaTec->getUltimaLectura() < $fichaTec->getUpdatedAt()) {
            // Buscar la petición en la caché de Redis
            $respj = $redis->get('indicador_'.$fichaTec->getId());
            if ($respj != null){
                $response->setContent($respj);
                    return $response;
            }
        }
        
        $resp = array();            

        $em = $this->getDoctrine()->getManager();

        $fichaRepository = $em->getRepository('IndicadoresBundle:FichaTecnica');

        $fichaRepository->crearIndicador($fichaTec);
        $resp = $fichaRepository->getDatosIndicador($fichaTec);
        $respj = json_encode($resp);

        //Guardar los datos en caché de redis            
        $response->setContent($respj);
        
        if ( is_array($resp['datos']) ){
            $redis->set('indicador_'.$fichaTec->getId(), $respj);
        }        

        return $response;
        
    }

    /**
     * @Get("/rest-service/indicadores", options={"expose"=true})
     * @Rest\View
     */
    public function getIndicadoresAction() {
        
        $response = new Response();
        $em = $this->getDoctrine()->getManager();

        $resp = array();
        

        //Recuperar todos los indicadores disponibles
        $indicadores = $em->getRepository("IndicadoresBundle:FichaTecnica")->findBy(array(), array('nombre' => 'ASC'));
        
        foreach($indicadores as $ind){
            $indicador = array();
            
            $indicador['id'] = $ind->getId();
            $indicador['nombre'] = $ind->getNombre();
            $indicador['unidadMedida'] = $ind->getUnidadMedida();
            $indicador['formula'] = $ind->getFormula();
            
            $variables = array();
            foreach($ind->getVariables() as $var){
                $variables[] = array('iniciales'=>$var->getIniciales(), 'nombre'=>$var->getNombre());
            }
            $indicador['variables'] = $variables;
                        
            $indicador['dimensiones'] = $ind->getCamposIndicador();
            
            $resp[] = $indicador;
        }        

        $response->setContent(json_encode($resp));

        return $response;
    }

}
