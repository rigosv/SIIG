<?php

namespace MINSAL\GridFormBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class IndicadorAdminController extends Controller
{   
    private $meses = array(1 => 'Ene.',
            2 => 'Feb.',
            3 => 'Mar.',
            4 => 'Abr.',
            5 => 'May.',
            6 => 'Jun.',
            7 => 'Jul.',
            8 => 'Ago.',
            9 => 'Sep.',
            10 => 'Oct.',
            11 => 'Nov.',
            12 => 'Dic.'
        );
    
    public function tableroCalidadAction(Request $request)
    {                
        $em = $this->getDoctrine()->getManager();
        $deptosC = $em->getConnection()->query("SELECT id, descripcion FROM ctl_departamentos WHERE id <= 14")->fetchAll();
        
        return $this->render('GridFormBundle:TableroCalidad:tablero.html.twig', array('departamentos'=>$deptosC));
    }
    
    public function tableroGeneralCalidadAction(Request $request)
    {                
        $em = $this->getDoctrine()->getManager();
        $deptosC = $em->getConnection()->query("SELECT id, descripcion FROM ctl_departamentos WHERE id <= 14")->fetchAll();
        
        $periodos_ = $em->getRepository('GridFormBundle:Formulario')->getPeriodosEvaluacion();
        $periodos = array();
        foreach ($periodos_ as $f) {
            $f['etiqueta'] = $this->meses[$f['mes']] . '/' . $f['anio'];
            $periodos[] = $f;
        }
        
        return $this->render('GridFormBundle:TableroCalidad:tablero-general.html.twig', array('departamentos'=>$deptosC, 'periodos'=>$periodos));
    }
}
