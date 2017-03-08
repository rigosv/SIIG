<?php

namespace MINSAL\CalidadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MINSAL\CalidadBundle\Entity\PlanMejora;
use MINSAL\CalidadBundle\Entity\Criterio;
use MINSAL\CalidadBundle\Entity\Actividad;

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
            $prioridades[$p->getId()] = $p->getDescripcion();
        }

        $tiposIntervencion = array();
        foreach ($em->getRepository('CalidadBundle:TipoIntervencion')->findBy(array(), array('codigo' => 'ASC')) as $p) {
            $tiposIntervencion[$p->getId()] = $p->getDescripcion();
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

        $criteriosEstandar = $em->getRepository('CalidadBundle:Estandar')->getCriterios($codigoEstructura, $periodo, $codigoFormulario);

        $criteriosParaPlan = array();

        $limiteAceptacion = 80;

        foreach ($criteriosEstandar['datos'][$codigoFormulario]['resumen_criterios'] as $c) {

            if ($c['porc_cumplimiento'] < $limiteAceptacion) {
                $c['brecha'] = $limiteAceptacion - $c['porc_cumplimiento'];
                array_push($criteriosParaPlan, $c);
            }
        }

        //Guardar los criterios, por si hay nuevos
        $em->getRepository('CalidadBundle:PlanMejora')->agregarCriterios($planMejora, $criteriosParaPlan);

        //Recuperar los criterios del plan
        $criterios = array();
        foreach ($planMejora->getCriterios() as $c) {
            $criterios['rows'][] = array('id' => $c->getId(),
                'descripcion' => $c->getVariableCaptura()->getDescripcion(),
                'brecha' => $c->getBrecha(),
                'causaBrecha' => $c->getCausaBrecha(),
                'oportunidadMejora' => $c->getOportunidadMejora(),
                'factoresMejoramiento' => $c->getFactoresMejoramiento(),
                'tipoIntervencion' => ( $c->getTipoIntervencion() === null) ? null : $c->getTipoIntervencion()->getId(),
                'prioridad' => ( $c->getPrioridad() === null ) ? null : $c->getPrioridad()->getId()
            );
        }


        return new Response(json_encode($criterios));
    }

    /**
     * @Route("/{criterio}/actividades" , name="calidad_planmejora_get_actividades", options={"expose"=true})
     */
    public function actividadesAction(Criterio $criterio) {

        //Recuperar los criterios del plan
        $actividades = array();
        foreach ($criterio->getActividades() as $a) {
            $actividades['rows'][] = array('id' => $a->getId(),
                'nombre' => $a->getNombre(),
                'fechaInicio' => $a->getFechaInicio()->format('d/m/Y'),
                'fechaFinalizacion' => $a->getFechaFinalizacion()->format('d/m/Y'),
                'medioVerificacion' => $a->getMedioVerificacion(),
                'responsable' => $a->getResponsable(),
                'porcentajeAvance' => $a->getPorcentajeAvance()
            );
        }
        return new Response(json_encode($actividades));
    }

    /**
     * @Route("/detalle/{criterio}/actividad/guardar" , name="calidad_planmejora_set_actividad", options={"expose"=true})
     */
    public function setActividadAction(Criterio $criterio, Request $req) {

        $em = $this->getDoctrine()->getManager();

        if ($req->get('oper') === 'add') {
            $actividad = new Actividad();
            $actividad->setCriterio($criterio);
        } else {
            $actividad = $em->find('CalidadBundle:Actividad', $req->get('id'));

            if ($req->get('oper') === 'del') {
                $em->remove($actividad);
            } else {
                $fi = new \DateTime();
                $ff = new \DateTime();

                $actividad->setNombre($req->get('nombre'));
                $actividad->setFechaInicio($fi->createFromFormat('d/m/Y', $req->get('fechaInicio')));
                $actividad->setFechaFinalizacion($ff->createFromFormat('d/m/Y', $req->get('fechaFinalizacion')));
                $actividad->setMedioVerificacion($req->get('medioVerificacion'));
                $actividad->setResponsable($req->get('responsable'));
                $actividad->setPorcentajeAvance($req->get('porcentajeAvance'));
                
                $em->persist($actividad);
            }
        }
        

        $em->flush();

        //Si todo sale bien devolver el id de la actividad (escencial para nuevas actividades)
        return new Response(json_encode(array("id" => $actividad->getId())));
    }

    /**
     * @Route("/criterio/guardar" , name="calidad_planmejora_set_criterio", options={"expose"=true})
     */
    public function setCriterioAction(Request $req) {

        $em = $this->getDoctrine()->getManager();

        $criterio = $em->find('CalidadBundle:Criterio', $req->get('id'));
        $prioridad = $em->find('CalidadBundle:Prioridad', $req->get('prioridad'));
        $tipoIntervencion = $em->find('CalidadBundle:TipoIntervencion', $req->get('tipoIntervencion'));

        $criterio->setCausaBrecha($req->get('causaBrecha'));
        $criterio->setOportunidadMejora($req->get('oportunidadMejora'));
        $criterio->setFactoresMejoramiento($req->get('factoresMejoramiento'));
        $criterio->setPrioridad($prioridad);
        $criterio->setTipoIntervencion($tipoIntervencion);

        $em->persist($criterio);
        $em->flush();

        return new Response(json_encode(array("ok" => "ok")));
    }

}
