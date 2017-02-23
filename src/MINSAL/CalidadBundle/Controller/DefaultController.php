<?php

namespace MINSAL\CalidadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class DefaultController extends Controller
{
    /**
     * @Route("/calidad")
     */
    public function indexAction()
    {
        return $this->render('CalidadBundle:Default:index.html.twig');
    }
}
