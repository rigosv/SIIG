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
            $prioridades[$p->getCodigo()] = $p->getDescripcion();
        }

        $tiposIntervencion = array();
        foreach ($em->getRepository('CalidadBundle:TipoIntervencion')->findBy(array(), array('codigo' => 'ASC')) as $p) {
            $tiposIntervencion[$p->getCodigo()] = $p->getDescripcion();
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

        $criterios = $em->getRepository('CalidadBundle:Estandar')->getCriterios($codigoEstructura, $periodo, $codigoFormulario);

        $criteriosParaPlan = array();

        foreach ($criterios['datos'][$codigoFormulario]['resumen_criterios'] as $c) {
            if ($c['porc_cumplimiento'] < 80){
                array_push($criteriosParaPlan, $c);
            }
        }
        
        //Guardar los criterios, por si hay nuevos
        $em->getRepository('CalidadBundle:PlanMejora')->agregarCriterios($planMejora, $criteriosParaPlan);
        /* $criterios = '{"rows":[
          {"id":"ALFKI","descripcion":"Alfreds Futterkiste", "brecha": 10},
          {"id":"ANATR","descripcion":"Ana Trujillo Emparedados y helados", "brecha": 5},
          {"id":"ANTON","descripcion":"Antonio Moreno Taquer\u00eda","brecha": 6},
          {"id":"BLAUS","descripcion":"Blauer See Delikatessen","brecha": 20},
          {"id":"BLONP","descripcion":"Blondel p\u00e8re et fils","brecha": 1},
          {"id":"BONAP","descripcion":"Bon app","brecha": 15},
          {"id":"BOTTM","descripcion":"Bottom-Dollar Markets","brecha": 23},
          {"id":"BSBEV","descripcion":"Bs Beverages","brecha": 30},
          {"id":"CACTU","descripcion":"Cactus Comidas para llevar", "brecha": 50},
          {"id":"CHOPS","descripcion":"Chop-suey Chinese","brecha": 60}

          ]}';
         * 
         */
        return new Response(
                $criterios
        );
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
     * @Route("/{id}/criterio/guardar" , name="calidad_planmejora_set_criterio", options={"expose"=true})
     */
    public function setCriterioAction(PlanMejora $planMejora, Request $req) {

        $resp = array("ok" => "ok");
        return new Response(
                json_encode($resp)
        );
    }

}
