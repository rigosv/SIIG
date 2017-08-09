<?php

namespace MINSAL\IndicadoresBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use MINSAL\IndicadoresBundle\Entity\FichaTecnica;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\Get;
use Predis;
use Symfony\Component\HttpFoundation\Request;

class IndicadorRESTController extends Controller {
    
    protected $tamanio = 200000; 

    /**
     * @param integer $fichaTec
     * @param string $dimension
     * @Get("/rest-service/data/{id}/{dimension}", options={"expose"=true})
     * @Rest\View
     */
    public function getIndicadorAction(FichaTecnica $fichaTec, $dimension, Request $request) {
        $response = new Response();
        $redis = new Predis\Client();
        $t = $this->get('translator');
        
        $filtro = $request->get('filtro');
        $verSql = ($request->get('ver_sql') == 'true') ? true : false;
        $verAnalisisDescriptivo = ($request->get('analisis_descriptivo') == 'true') ? true : false;
        $hash = md5($filtro.$verSql);
        
        // verifica que la respuesta no se ha modificado para la petición dada
        if ($fichaTec->getUpdatedAt() != '' and $fichaTec->getUltimaLectura() != '' and $fichaTec->getUltimaLectura() < $fichaTec->getUpdatedAt()
                and $verAnalisisDescriptivo == false ) {
            // Buscar la petición en la caché de Redis
            $respj = $redis->get('indicador_'.$fichaTec->getId().'_'.$dimension.$hash);            
            if ($respj != null){
                $dat = json_decode($respj);
                if ( count($dat->datos) > 0){
                    $response->setContent($respj);
                    return $response;
                }
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
        
        if ( is_array($resp['datos']) ){
            $redis->set('indicador_'.$fichaTec->getId().'_'.$dimension.$hash, $respj);
        }
        
        if ($verAnalisisDescriptivo){
            $sql = $resp['datos'];            
            $dimensionObj = $em->getRepository('IndicadoresBundle:SignificadoCampo')->findOneBy(array('codigo'=>$dimension));

            $datos = array_pop($fichaRepository->getAnalisisDescriptivo($sql));
            
            $tabla = "
                <TABLE CLASS= 'table table-striped'>
                    <THEAD>
                        <TR>
                            
                            <TH>".$t->trans('_promedio_')."</TH>
                            <TH>".$t->trans('_desviacion_estandar_')."</TH>
                            <TH>".$t->trans('_maximo_')."</TH>
                            <TH>".$t->trans('_tercer_cuartil_')."</TH>
                            <TH>".$t->trans('_segundo_cuartil_mediana_')."</TH>
                            <TH>".$t->trans('_primer_cuartil_')."</TH>
                            <TH>".$t->trans('_minimo_')."</TH>
                        </TR>
                    </THEAD>
                    <TBODY>
                        ";
            //foreach ($datos AS $d){
                $tabla .= "
                        <TR>
                            
                            <TD>$datos[promedio]</TD>
                            <TD>$datos[desviacion_estandar]</TD>
                            <TD>$datos[max]</TD>
                            <TD>$datos[cuartil_3]</TD>
                            <TD>$datos[cuartil_2]</TD>
                            <TD>$datos[cuartil_1]</TD>
                            <TD>$datos[min]</TD>
                        </TR>
                ";
            //}
            $tabla .= " 
                    </TBODY>
                </TABLE>
                ";
            $resp['datos'] = $tabla;
            $respj = json_encode($resp);
        }
        
        

        $response->setContent($respj);
        return $response;
        
    }
    
    /**
     * Obtener los datos del indicador sin aplicar la fórmula ni filtros
     * @param integer $fichaTec
     * @param string $dimension
     * @Get("/rest-service/data/{id}", options={"expose"=true})
     * @Rest\View
     */
    public function getDatosIndicadorAction(FichaTecnica $fichaTec, Request $request) {
        $response = new Response();        
                
        $redis = new Predis\Client();
        
        $parte = $request->get('parte');
        
        if ($fichaTec->getUpdatedAt() != '' and $fichaTec->getUltimaLectura() != '' and $fichaTec->getUltimaLectura() < $fichaTec->getUpdatedAt()) {
            // Buscar la petición en la caché de Redis
            $porcion = ( $parte == null) ? 0 : ( $parte -1 ) * $this->tamanio ;
            $respj = $redis->get('indicador_'.$fichaTec->getId().'_parte_'.$porcion);
            if ($respj != null){
                //$respParte = $this->obtenerParteRespuesta(json_decode($respj), $parte, $request);
                $resp = '{"datos": '. $respj.', "total_partes": "'. $redis->get('indicador_'.$fichaTec->getId().'_tamanio_') . '"}';
                $response->setContent($resp);
                return $response;
            }
        }
        
        $resp = array();            

        $em = $this->getDoctrine()->getManager();

        $fichaRepository = $em->getRepository('IndicadoresBundle:FichaTecnica');

        $fichaRepository->crearIndicador($fichaTec);
        $resp = $fichaRepository->getDatosIndicador($fichaTec);
        //$respj = json_encode($resp);

        //Guardar los datos en caché de redis            
        //$response->setContent($respj);
        
        if ( is_array($resp) ){
            //Guardarlo en caché por partes
            $total_partes = ceil( count($resp) / $this->tamanio );
            
            for ($parteX = 1 ; $parteX <= $total_partes ; $parteX++){
                $porcion = ( $parteX -1 ) * $this->tamanio ;

                $porcionDatos = array_slice($resp, $porcion, $this->tamanio);
                
                $redis->set('indicador_'.$fichaTec->getId().'_tamanio_', $total_partes);
                $redis->set('indicador_'.$fichaTec->getId().'_parte_'.$porcion, json_encode($porcionDatos));
            }
        }        

        $respParte = $this->obtenerParteRespuesta($resp, $parte, $request);
        $response->setContent($respParte);
        return $response;
        
    }
    
    private function obtenerParteRespuesta($datos, $parte, Request $request) {
        $tamanio = $this->tamanio;
        //Verificar si son más de $tamanio registros y si es una consulta ajax
        if ($request->isXmlHttpRequest() and ( count($datos) > $tamanio or $parte != null ) ){
            $total_partes = ceil( count($datos) / $tamanio );
            $porcion = ( $parte == null) ? 0 : ( $parte -1 ) * $tamanio ;
            
            $porcionDatos = array_slice($datos, $porcion, $tamanio);
            $resp['datos'] = $porcionDatos;
            $resp['total_partes'] = $total_partes;
            
        } else {
            $resp['datos'] = $datos;
            $resp['total_partes'] = 1;
            
        }
        return json_encode($resp);
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
