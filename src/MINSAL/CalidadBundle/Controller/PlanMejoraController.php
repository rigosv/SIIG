<?php

namespace MINSAL\CalidadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MINSAL\CalidadBundle\Entity\PlanMejora;

/**
 * Institucion controller.
 *
 * @Route("/calidad/planmejora")
 */
class PlanMejoraController extends Controller {

    /**
     * @Route("/crear")
     */
    public function indexAction() {
        return $this->render('CalidadBundle:PlanMejora:index.html.twig');
    }

    /**
     * @Route("/{id}/detalle/")
     */
    public function detalleAction(PlanMejora $planMejora) {
        $admin_pool = $this->get('sonata.admin.pool');
        $em = $this->getDoctrine()->getManager();

        $prioridades = array();
        foreach ($em->getRepository('CalidadBundle:Prioridad')->findBy(array(), array('codigo' => 'ASC')) as $p) {
            $prioridades[$p->getId()] = $p->getDescripcion();
        }

        $tiposIntervencion = array();
        foreach ($em->getRepository('CalidadBundle:TipoIntervencion')->findBy(array(), array('codigo' => 'ASC')) as $p) {
            $tiposIntervencion[$p->getId()] = $p->getDescripcion();
        }

        return $this->render('CalidadBundle:PlanMejora:detalle.html.twig', array('admin_pool' => $admin_pool,
                    'prioridades' => json_encode($prioridades),
                    'tiposIntervencion' => json_encode($tiposIntervencion),
                    'planMejora' => $planMejora
                        )
        );
    }

    /**
     * @Route("/{id}/criterios" , name="calidad_planmejora_get_criterios", options={"expose"=true})
     */
    public function criteriosAction(PlanMejora $planMejora) {
        $em = $this->getDoctrine()->getManager();

        $codigoEstructura = $planMejora->getEstablecimiento()->getCodigo();
        $periodo = $planMejora->getPeriodo()->getAnio() . '_' . $planMejora->getPeriodo()->getMes();
        $codigoFormulario = $planMejora->getEstandar()->getFormularioCaptura()->getCodigo();

        $criteriosEstandar = $em->getRepository('CalidadBundle:Estandar')->getCriterios($codigoEstructura, $periodo, $codigoFormulario);

        $criteriosParaPlan = array();
        
        $limiteAceptacion = 80;
        
        foreach ($criteriosEstandar['datos'][$codigoFormulario]['resumen_criterios'] as $c) {
            
            if ($c['porc_cumplimiento'] < $limiteAceptacion){
                $c['brecha'] = $limiteAceptacion - $c['porc_cumplimiento'];
                array_push($criteriosParaPlan, $c);
            }
        }
        
        //Guardar los criterios, por si hay nuevos
        $em->getRepository('CalidadBundle:PlanMejora')->agregarCriterios($planMejora, $criteriosParaPlan);

        //Recuperar los criterios del plan
        $criterios = array();
        foreach ($planMejora->getCriterios() as $c ){
            $criterios['rows'][] = array('id'=> $c->getId(),
                'descripcion'=> $c->getVariableCaptura()->getDescripcion(),
                'brecha'=> $c->getBrecha(),
                'causaBrecha'=> $c->getCausaBrecha(),
                'oportunidadMejora' => $c->getOportunidadMejora(),
                'factoresMejoramiento'=> $c->getFactoresMejoramiento(),
                'tipoIntervencion'=> ( $c->getTipoIntervencion() === null) ?  null : $c->getTipoIntervencion()->getId(),
                'prioridad'=> ( $c->getPrioridad() === null ) ? null : $c->getPrioridad()->getId());
        }
        
        
        return new Response(json_encode($criterios));
    }

    /**
     * @Route("/detalle/{criterio}/actividades" , name="calidad_planmejora_get_actividades", options={"expose"=true})
     */
    public function actividadesAction($criterio, Request $req) {

        $criterios = ($criterio == '0') ? '{}' : '{"rows":[{"OrderID":"11058","RequiredDate":"1998-05-27 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"31.1400"},{"OrderID":"10956","RequiredDate":"1998-04-28 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"44.6500"},{"OrderID":"10853","RequiredDate":"1998-02-24 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"53.8300"},{"OrderID":"10614","RequiredDate":"1997-08-26 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"1.9300"},{"OrderID":"10582","RequiredDate":"1997-07-25 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"27.7100"},{"OrderID":"10509","RequiredDate":"1997-05-15 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"0.1500"},{"OrderID":"10501","RequiredDate":"1997-05-07 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"8.8500"}]}';
        return new Response(
                $criterios
        );
    }

    /**
     * @Route("/detalle/{criterio}/actividad/guardar" , name="calidad_planmejora_set_actividad", options={"expose"=true})
     */
    public function setActividadAction($criterio, Request $req) {

        $criterios = ($criterio == '0') ? '{}' : '{"rows":[{"OrderID":"11058","RequiredDate":"1998-05-27 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"31.1400"},{"OrderID":"10956","RequiredDate":"1998-04-28 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"44.6500"},{"OrderID":"10853","RequiredDate":"1998-02-24 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"53.8300"},{"OrderID":"10614","RequiredDate":"1997-08-26 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"1.9300"},{"OrderID":"10582","RequiredDate":"1997-07-25 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"27.7100"},{"OrderID":"10509","RequiredDate":"1997-05-15 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"0.1500"},{"OrderID":"10501","RequiredDate":"1997-05-07 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"8.8500"}]}';
        return new Response(
                $criterios
        );
    }

    /**
     * @Route("/criterio/guardar" , name="calidad_planmejora_set_criterio", options={"expose"=true})
     */
    public function setCriterioAction(Request $req) {
        
        $em = $this->getDoctrine()->getManager();
        
        $criterio = $em->find('CalidadBundle:Criterio', $req->get('id'));
        $prioridad = $em->find('CalidadBundle:Prioridad', $req->get('prioridad'));
        $tipoIntervencion = $em->find('CalidadBundle:TipoIntervencion', $req->get('tipoIntervencion'));
        
        $criterio->setCausaBrecha($req->get('causaBrecha'));
        $criterio->setOportunidadMejora($req->get('oportunidadMejora'));
        $criterio->setFactoresMejoramiento($req->get('factoresMejoramiento'));
        $criterio->setPrioridad($prioridad);
        $criterio->setTipoIntervencion($tipoIntervencion);
        
        $em->persist($criterio);
        $em->flush();
        
        $resp = array("ok" => "ok");
        return new Response(
                json_encode($resp)
        );
    }

}
