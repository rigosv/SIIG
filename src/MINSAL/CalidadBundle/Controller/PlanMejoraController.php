<?php

namespace MINSAL\CalidadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

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
        return $this->render('CalidadBundle:Default:index.html.twig');
    }
}
