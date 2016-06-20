<?php

namespace MINSAL\GridFormBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class IndicadorAdminController extends Controller
{           
    public function tableroCalidadAction(Request $request)
    {                
        return $this->render('GridFormBundle:TableroCalidad:tablero.html.twig', array());
    }
    
    public function tableroGeneralCalidadAction(Request $request)
    {                
        return $this->render('GridFormBundle:TableroCalidad:tablero-general.html.twig', array());
    }
}
