<?php

namespace MINSAL\CostosBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class EstructuraController extends Controller
{
    /**
     * @Route("/estructura", name="get_estructura_organizativa", options={"expose"=true})     
     * @Template()
     */
    public function getEstructuraOrganizativaAction()
    {
        $response = new Response();
        $em = $this->getDoctrine()->getManager();
        $estructura = $em->getRepository("CostosBundle:Estructura")->findBy(array(), array('nombre' => 'ASC'));

        $datos =  array();
        foreach ($estructura as $e){
            $padre = ($e->getParent()) ? $e->getParent()->getId() : -1;
            $datos[] = array('id'=>$e->getId(), 
                            'parentid'=>$padre, 
                            'text'=>$e->getNombre(),
                            'value' => $e->getId()
                        );
        }
        $datos = json_encode($datos);
        
        $response->setContent($datos);
        
        return $response;
    }
    
}
