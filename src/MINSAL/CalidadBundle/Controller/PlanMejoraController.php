<?php

namespace MINSAL\CalidadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

/**
 * Institucion controller.
 *
 * @Route("/calidad/planmejora")
 */
class PlanMejoraController extends Controller
{
    /**
     * @Route("/crear")
     */
    public function indexAction()
    {
        return $this->render('CalidadBundle:PlanMejora:index.html.twig');
    }
    
    /**
     * @Route("/detalle")
     */
    public function crearDetalleAction()
    {
        $admin_pool = $this->get('sonata.admin.pool');
        
        return $this->render('CalidadBundle:PlanMejora:detalle.html.twig', array ('admin_pool' => $admin_pool));
    }
    
    /**
     * @Route("/detalle/criterios" , name="calidad_planmejora_get_criterios", options={"expose"=true})
     */
    public function criteriosAction()
    {
        $criterios = '{"rows":[
		{"CustomerID":"ALFKI","CompanyName":"Alfreds Futterkiste","ContactName":"Maria Anders","Phone":"030-0074321","City":"Berlin"},
		{"CustomerID":"ANATR","CompanyName":"Ana Trujillo Emparedados y helados","ContactName":"Ana Trujillo","Phone":"(5) 555-4729","City":"M\u00e9xico D.F."},
		{"CustomerID":"ANTON","CompanyName":"Antonio Moreno Taquer\u00eda","ContactName":"Antonio Moreno","Phone":"(5) 555-3932","City":"M\u00e9xico D.F."},
		{"CustomerID":"BLAUS","CompanyName":"Blauer See Delikatessen","ContactName":"Hanna Moos","Phone":"0621-08460","City":"Mannheim"},
		{"CustomerID":"BLONP","CompanyName":"Blondel p\u00e8re et fils","ContactName":"Fr\u00e9d\u00e9rique Citeaux","Phone":"88.60.15.31","City":"Strasbourg"},
		{"CustomerID":"BONAP","CompanyName":"Bon app","ContactName":"Laurence Lebihan","Phone":"91.24.45.40","City":"Marseille"},
		{"CustomerID":"BOTTM","CompanyName":"Bottom-Dollar Markets","ContactName":"Elizabeth Lincoln","Phone":"(604) 555-4729","City":"Tsawassen"},
		{"CustomerID":"BSBEV","CompanyName":"Bs Beverages","ContactName":"Victoria Ashworth","Phone":"(171) 555-1212","City":"London"},
		{"CustomerID":"CACTU","CompanyName":"Cactus Comidas para llevar","ContactName":"Patricio Simpson","Phone":"(1) 135-5555","City":"Buenos Aires"},
		{"CustomerID":"CHOPS","CompanyName":"Chop-suey Chinese","ContactName":"Yang Wang","Phone":"(5) 555-3392","City":"Bern"}
		
                ]}';
        return new Response(
                $criterios
        );
    }
    
    /**
     * @Route("/detalle/{criterio}/actividades" , name="calidad_planmejora_get_actividades", options={"expose"=true})
     */
    public function actividadesAction($criterio, Request $req)
    {
        
        $criterios = ($criterio == '0') ? '{}' : '{"rows":[{"OrderID":"11058","RequiredDate":"1998-05-27 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"31.1400"},{"OrderID":"10956","RequiredDate":"1998-04-28 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"44.6500"},{"OrderID":"10853","RequiredDate":"1998-02-24 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"53.8300"},{"OrderID":"10614","RequiredDate":"1997-08-26 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"1.9300"},{"OrderID":"10582","RequiredDate":"1997-07-25 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"27.7100"},{"OrderID":"10509","RequiredDate":"1997-05-15 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"0.1500"},{"OrderID":"10501","RequiredDate":"1997-05-07 00:00:00","ShipName":"Blauer See Delikatessen","ShipCity":"Mannheim","Freight":"8.8500"}]}';
        return new Response(
                $criterios
        );
    }
}
