<?php

namespace MINSAL\GridFormBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use MINSAL\GridFormBundle\Entity\Formulario;

class IndicadorController extends Controller
{
    /**
     * @Route("/formulario/{id}/criterios", name="get_criterios_estandar", options={"expose"=true})
     * @Template()
     */
    public function getCriteriosIndicadorAction(Formulario $Frm)
    {
        //$em = $this->getDoctrine()->getManager();
        $response = new Response();
        
        $criterios = $Frm->getVariables();

        $respuesta = (count($criterios) > 0) ? $criterios : array();
        
        $response->setContent($respuesta);
            
        return $response;
    }
}
