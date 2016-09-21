<?php

namespace MINSAL\GridFormBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class IndicadorAdminController extends Controller
{           
    public function tableroCalidadAction(Request $request)
    {                
        $em = $this->getDoctrine()->getManager();
        $deptosC = $em->getConnection()->query("SELECT id, descripcion FROM ctl_departamentos WHERE id <= 14")->fetchAll();
        
        return $this->render('GridFormBundle:TableroCalidad:tablero.html.twig', array('departamentos'=>$deptosC));
    }
    
    public function tableroGeneralCalidadAction(Request $request)
    {                
        return $this->render('GridFormBundle:TableroCalidad:tablero-general.html.twig', array());
    }
}
