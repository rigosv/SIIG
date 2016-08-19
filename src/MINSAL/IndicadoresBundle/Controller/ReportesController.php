<?php

namespace MINSAL\IndicadoresBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


/**
* @Route("/reportes")
*/
class ReportesController extends Controller {

    /**
     * @Route("/matriz_seguimiento", name="matriz-seguimiento")
     */
    public function matrizSeguimientoAccion() {
        $admin_pool = $this->get('sonata.admin.pool');

        return $this->render('IndicadoresBundle:Reportes:matrizSeguimiento.html.twig', 
                                array(
                                    'admin_pool' => $admin_pool
                                ));
    }
}
