<?php

namespace MINSAL\CalidadBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use MINSAL\CalidadBundle\Entity\PlanMejora;
use MINSAL\CalidadBundle\Entity\Criterio;
use MINSAL\CalidadBundle\Entity\Actividad;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;

/**
 * Institucion controller.
 *
 * @Route("/calidad/planmejora")
 */
class PlanMejoraController extends Controller {

    /**
     * @Route("/", name="calidad_planmejora")
     */
    public function indexAction(Request $request) {
        $admin_pool = $this->get('sonata.admin.pool');
        $establecimiento = null;
        $estandaresEval = null;
        $evaluacionesNumTot = array();
        $estandaresNumEval = null;
        $periodo = null;
        $em = $this->getDoctrine()->getManager();

        $datos = array();
        $formB = $this->createFormBuilder()
                ->add('periodo', EntityType::class, array(
                    'label' => '_periodo_evaluacion_',
                    'class' => 'GridFormBundle:PeriodoIngreso',
                    'query_builder' => function (EntityRepository $er) {
                        return $er->getPeriodosEvaluadosCalidad();
                    },
                ))
                ->add('continuar', SubmitType::class, array('label' => '_cargar_', 'attr' => array('class' => 'btn btn-success')))
        ;

        $form = $formB->getForm();
        $form->handleRequest($request);


        if ($form->isSubmitted()) {
            $datos = $form->getData();
            $periodo = $datos['periodo'];

            if ($this->getUser()->getUsername() === 'admin') {
                //Recuperar las unidades que tienen evaluaciones en el periodo seleccionado
                $formB->add('establecimiento', EntityType::class, array(
                    'class' => 'CostosBundle:Estructura',
                    'query_builder' => function (EntityRepository $er) use ($periodo) {
                        return $er->getEstablecimientosEvaluadosCalidad($periodo);
                    }
                ));
            } else {
                //El establecimiento será el asignado al usuario
                $establecimiento = $this->getUser()->getEstablecimientoPrincipal();
            }

            //Actualizar el formulario con el campo agregado
            $form = $formB->getForm();
            $form->handleRequest($request);
            $datos = $form->getData();

            //Verificar si ya se eligió la unidad (sería el segundo envio)
            if (array_key_exists('establecimiento', $datos)) {
                // por el administrador
                $establecimiento = $datos['establecimiento'];
            }

            if ($establecimiento !== null) {

                $per = $periodo->getAnio() . '_' . ltrim($periodo->getMes(), '0');
                // Evaluaciones por lista de chequeo
                $data = $em->getRepository('GridFormBundle:Indicador')->getEvaluaciones($establecimiento->getCodigo(), $per);

                //Crear un arreglo donde la llave sea el código del formulario
                // Para facilitar la búsqueda posterior
                $evaluaciones = array();
                foreach ($data as $d) {
                    $evaluaciones[$d['codigo']] = $d;
                }

                //Obtener las otras evaluaciones que no son lista de chequeo
                $dataNum = $em->getRepository('CalidadBundle:Estandar')->getIndicadoresEvaluadosNumericos($establecimiento, $periodo);
                
                //Crear un arreglo donde la llave sea el código del formulario
                // Solo si el estándar tiene algún indicador que no cumplió la meta
                $evaluacionesNum = array();
                foreach ($dataNum as $d) {
                    $evaluacionesNumTot[$d['codigo']]['evaluaciones'][] = $d;
                    if ($d['meta'] != '' and $d['color'] != 'green'){
                        $evaluacionesNum[$d['codigo']] = $d;
                        if (array_key_exists('reprobados', $evaluacionesNumTot[$d['codigo']] )){
                            $evaluacionesNumTot[$d['codigo']]['reprobados'] ++;
                        } else {
                            $evaluacionesNumTot[$d['codigo']]['reprobados'] = 1;
                        }
                    }
                    //Si no tuvo ninguna reprobada
                    if (!array_key_exists('reprobados', $evaluacionesNumTot[$d['codigo']] )) {
                        $evaluacionesNumTot[$d['codigo']]['reprobados'] = 0;
                    }
                }
                
                //Obtener los estándares
                $estadares = $em->getRepository('CalidadBundle:Estandar')->findBy(array(), array('posicion' => 'ASC'));

                //Obtener los estándares que fueron evaluados y que no cumplieron la meta
                $estandaresEval = array();
                foreach ($estadares as $est) {
                    $paraPlan = false;
                    $frm = $est->getFormularioCaptura();
                    $tipo = null;
                    if ($frm != null){
                        $tipo = (array_key_exists($frm->getCodigo(), $evaluaciones)) ? 'lista_chequeo' : null;
                        $tipo = (array_key_exists($frm->getCodigo(), $evaluacionesNum)) ? 'numerico': $tipo;
                    }
                                        
                    if ($tipo == 'lista_chequeo' and ( $est->getMeta() > $evaluaciones[$frm->getCodigo()]['calificacion'])) {
                        $paraPlan = true;
                        $estandaresEval[$est->getCodigo()]['est'] = $est;
                        $estandaresEval[$est->getCodigo()]['eval'] = $evaluaciones[$frm->getCodigo()];
                    } elseif ($tipo == 'numerico'){
                        $paraPlan = true;
                        $estandaresNumEval[$est->getCodigo()]['est'] = $est;
                        $estandaresNumEval[$est->getCodigo()]['eval'] = $evaluacionesNum[$frm->getCodigo()];
                    }
                    
                    if ($paraPlan){
                        //Verificar si tiene plan de mejora creado
                        $plan = $em->getRepository('CalidadBundle:PlanMejora')
                                ->findOneBy(
                                array('establecimiento' => $establecimiento,
                                    'periodo' => $periodo,
                                    'estandar' => $est
                                )
                        );
                        if ($tipo === 'numerico'){
                            $estandaresNumEval[$est->getCodigo()]['plan'] = $plan;
                        } else {
                            $estandaresEval[$est->getCodigo()]['plan'] = $plan;
                        }
                    }
                }
            }
            $formB->setData($datos);
        }

        return $this->render('CalidadBundle:PlanMejora:index.html.twig', array(
                    'admin_pool' => $admin_pool,
                    'form' => $formB->getForm()->createView(),
                    'establecimiento' => $establecimiento,
                    'periodo' => $periodo,
                    'estandaresEval' => $estandaresEval,
                    'estandaresNumEval' => $estandaresNumEval,
                    'estandaresNumTot' => $evaluacionesNumTot
        ));
    }

    /**
     * @Route("/crear/{id_establecimiento}/{anio}/{mes}/{id_estandar}", name="calidad_planmejora_crear")
     */
    public function crearAction($id_establecimiento, $anio, $mes, $id_estandar) {
        $em = $this->getDoctrine()->getManager();

        $establecimiento = $em->find('CostosBundle:Estructura', $id_establecimiento);
        $periodo = $em->find('GridFormBundle:PeriodoIngreso', array('anio' => $anio, 'mes' => $mes));
        $estandar = $em->find('CalidadBundle:Estandar', $id_estandar);

        $plan = new PlanMejora();
        $plan->setEstablecimiento($establecimiento);
        $plan->setEstandar($estandar);
        $plan->setPeriodo($periodo);

        $em->persist($plan);
        $em->flush();

        return $this->forward('CalidadBundle:PlanMejora:detalle', array(
                    'id' => $plan->getId()
        ));
    }

    /**
     * @Route("/{id}/detalle/", name="calidad_planmejora_detalle")
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
        $estandar = $planMejora->getEstandar();
        $codigoFormulario = $estandar->getFormularioCaptura()->getCodigo();

        $formaEvaluacion = $planMejora->getEstandar()->getFormaEvaluacion();
        
        $criteriosEvaluados = array();
        if ($formaEvaluacion == 'lista_chequeo'){
            $criteriosEstandar = $em->getRepository('CalidadBundle:Estandar')->getCriterios($codigoEstructura, $periodo, $codigoFormulario);
            $criteriosEvaluados = $criteriosEstandar['datos'][$codigoFormulario]['resumen_criterios'];
        } elseif ($formaEvaluacion == 'rango_colores'){
            //Obtener las otras evaluaciones que no son lista de chequeo
            $criteriosEvaluados = $em->getRepository('CalidadBundle:Estandar')
                    ->getIndicadoresEvaluadosNumericos($planMejora->getEstablecimiento(), $planMejora->getPeriodo(), $estandar);
        }
        
        $criteriosParaPlan = $this->getCriteriosParaPlan($formaEvaluacion, $criteriosEvaluados);

        //Guardar los criterios, por si hay nuevos
        $em->getRepository('CalidadBundle:PlanMejora')->agregarCriterios($planMejora, $criteriosParaPlan);

        //Recuperar los criterios del plan
        $criterios = array();
        foreach ($em->getRepository('CalidadBundle:Criterio')->findBy(array('planMejora' => $planMejora), array('variableCaptura' => 'ASC')) as $c) {
            $datos = array('id' => $c->getId(),
                'descripcion' => $c->getVariableCaptura()->getDescripcion(),
                'brecha' => $c->getBrecha(),
                'causaBrecha' => $c->getCausaBrecha(),
                'oportunidadMejora' => $c->getOportunidadMejora(),
                'factoresMejoramiento' => $c->getFactoresMejoramiento(),
                'tipoIntervencion' => ( $c->getTipoIntervencion() === null) ? null : $c->getTipoIntervencion()->getId(),
                'prioridad' => ( $c->getPrioridad() === null ) ? null : $c->getPrioridad()->getId(),
                'indicador' => null
            );
            if ($formaEvaluacion == 'rango_colores'){
                $datos['descripcion'] = $c->getVariableCaptura()->getArea()->getDescripcion();
                 $indicador = $c->getVariableCaptura()->getIndicadores();
                 $datos['indicador'] = $indicador[0]->getDescripcion();
            }
            $criterios['rows'][] = $datos;
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
        $t = $this->get('translator');

        if ($req->get('oper') === 'add') {
            $actividad = new Actividad();
            $actividad->setCriterio($criterio);
        } else {
            $actividad = $em->find('CalidadBundle:Actividad', $req->get('id'));
        }

        if ($req->get('oper') === 'del') {
            $em->remove($actividad);
        } else {
            $fecha = new \DateTime();
            $fi = $fecha->createFromFormat('d/m/Y', $req->get('fechaInicio'));
            $ff = $fecha->createFromFormat('d/m/Y', $req->get('fechaFinalizacion'));

            if ($fi > $ff) {
                return new Response(json_encode(array("error" => $t->trans('_la_fecha_de_inicio_debe_ser_menor_a_la_fecha_de_finalizacion_'))));
            }
            
            //Verificar que las fechas no sobrepasen el periodo de intervención del criterio 
            // Recuperar la menor fecha de las actividades 
            if ($criterio->getTipoIntervencion() == null){
                return new Response(json_encode(array("error" => $t->trans('_debe_definir_periodo_intervencion_del_criterio_'))));
            } elseif (! $this->estaDentroPeriodoIntervencion($criterio, $fi, $ff) ){
                return new Response(json_encode(array("error" => $t->trans('_duración_actividad_sobrepasa_duración_de_acuerdo_periodo_intervención_criterio_'))));
                
            }

            $actividad->setNombre($req->get('nombre'));
            $actividad->setFechaInicio($fi);
            $actividad->setFechaFinalizacion($ff);
            $actividad->setMedioVerificacion($req->get('medioVerificacion'));
            $actividad->setResponsable($req->get('responsable'));
            $actividad->setPorcentajeAvance($req->get('porcentajeAvance'));

            $em->persist($actividad);
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

    /**
     * @Route("/{id}/ver/", name="calidad_planmejora_ver")
     */
    public function verAction(PlanMejora $planMejora) {
        $admin_pool = $this->get('sonata.admin.pool');
        $em = $this->getDoctrine()->getManager();
        $formaEvaluacion = $planMejora->getEstandar()->getFormaEvaluacion();

        $criterios = $em->getRepository('CalidadBundle:PlanMejora')->getCriteriosOrden($planMejora);
        $establecimiento = $em->getRepository('CostosBundle:Estructura')->getEstablecimiento($planMejora->getEstablecimiento());
        
        if ($establecimiento == false){
            $establecimiento['region'] = '';
        }
        $indicadores = array();
        
        foreach ($criterios as $c){
            if ($planMejora->getEstandar()->getFormaEvaluacion() == 'rango_colores'){
                $ind =  $c->getVariableCaptura()->getIndicadores();
                $indicadores[$ind[0]->getCodigo()]['descripcion'] = $ind[0]->getDescripcion();
                $indicadores[$ind[0]->getCodigo()]['criterios'][] = $c;
            } else {
                $indicadores[0]['descripcion'] = '';
                $indicadores[0]['criterios'][] = $c;
            }
        }
        
        $historialCriterios = $this->getHistorialCriterios($planMejora, $indicadores);
        
        return $this->render('CalidadBundle:PlanMejora:ver.html.twig', array('admin_pool' => $admin_pool,
                    'plan' => $planMejora,
                    'indicadores' => $indicadores,
                    'historialCriterios' => $historialCriterios,
                    'establecimiento' => $establecimiento
                        )
        );
    }
    
    public function getHistorialCriterios(PlanMejora $planMejora, $indicadores) {
       $em = $this->getDoctrine()->getManager();
       
        $arrCriterios = array();
        foreach ($indicadores as $ind) {
            foreach ($ind['criterios'] as $c){
                $arrCriterios[] = $c->getVariableCaptura()->getCodigo();
            }
        }
        $formaEvaluacion = $planMejora->getEstandar()->getFormaEvaluacion();
        $historialCriterios = array();
        $limiteAceptacion = 80;
        //Historial de criterios
        $codigoEstructura = $planMejora->getEstablecimiento()->getCodigo();
        $periodoInicial = $planMejora->getPeriodo();
        $codigoFormulario = $planMejora->getEstandar()->getFormularioCaptura()->getCodigo();
        $anio = $periodoInicial->getAnio();
        $mes = (int) $periodoInicial->getMes();
        
        if ($formaEvaluacion == 'lista_chequeo'){
            for ($i = 0; $i < 7; $i++) {
                //Periodo anterior
                $anio = ($mes == 1) ? $anio - 1 : $anio;
                $mes = ($mes == 1) ? 12 : $mes - 1;


                //Buscar la evaluacion de los criterios para ese periodo
                $criteriosEstandar = $em->getRepository('CalidadBundle:Estandar')
                        ->getCriterios($codigoEstructura, $anio . '_' . str_pad($mes, 2, "0", STR_PAD_LEFT), $codigoFormulario);

                //Recuperar los criterios que están en el plan actual
                if (count($criteriosEstandar['datos']) > 0){
                    foreach ($criteriosEstandar['datos'][$codigoFormulario]['resumen_criterios'] as $c) {
                        if (in_array($c['codigo_variable'], $arrCriterios)) {
                            $brecha = ( $c['porc_cumplimiento'] > $limiteAceptacion ) ? 0 : $limiteAceptacion - $c['porc_cumplimiento'];
                            $historialCriterios[$c['codigo_variable']][ str_pad($mes, 2, "0", STR_PAD_LEFT).'/'.$anio] = $brecha;
                        }
                    }
                }
            }
        } else {
            $criteriosEvaluados = $em->getRepository('CalidadBundle:Estandar')->getIndicadoresEvaluadosNumericos($planMejora->getEstablecimiento(), $planMejora->getPeriodo());
            if (count($criteriosEvaluados) > 0){
                foreach ($criteriosEvaluados as $c) {
                    if (in_array($c['codigo_variable'], $arrCriterios)) {
                        $historial = explode(',', str_replace(array('{', '}'), array('', ''), $c['historial']));
                        $limites = explode('-', $c['meta']);
                        
                        foreach ($historial as $h){
                            list($mes, $anio, $calificacion) = explode('/', $h);
                            if ($calificacion < $limites[0] ){
                                $brecha = $limites[0] - $calificacion;
                            } elseif ($calificacion > $limites[1]){
                                $brecha = $calificacion - $limites[1];
                            }
                            $historialCriterios[$c['codigo_variable']][ str_pad($mes, 2, "0", STR_PAD_LEFT).'/'.$anio] = $brecha;
                        }
                    }
                }
            }
        }
        
        return $historialCriterios;
    }
    
    public function getCriteriosParaPlan($formaEvaluacion, $criterios) {
        $criteriosParaPlan = array();
        
        if ($formaEvaluacion == 'lista_chequeo'){
            $limiteAceptacion = 80;
            foreach ($criterios as $c) {
                if ($c['porc_cumplimiento'] < $limiteAceptacion) {
                    $c['brecha'] = $limiteAceptacion - $c['porc_cumplimiento'];
                    array_push($criteriosParaPlan, $c);
                }
            }
        } elseif ($formaEvaluacion == 'rango_colores') {
            foreach ($criterios as $c) {
                if ($c['meta'] != '' and $c['color'] != 'green'){
                    $limites = explode('-', $c['meta']);
                    if ($c['calificacion'] < $limites[0] ){
                        $c['brecha'] = $limites[0] - $c['calificacion'];
                    } elseif ($c['calificacion'] > $limites[1]){
                        $c['brecha'] = $c['calificacion'] - $limites[1];
                    }
                    array_push($criteriosParaPlan, $c);
                }
            }
        }
        return $criteriosParaPlan;
    }
    
    public function estaDentroPeriodoIntervencion($criterio, $fi, $ff) {
        $em = $this->getDoctrine()->getManager();
        
        $actividades = $em->getRepository('CalidadBundle:Actividad')->findBy(array('criterio'=>$criterio), array('fechaInicio'=>'ASC'));
        $result = true;    
        
        $menorFechaReg = (count($actividades) > 0) ? $actividades[0]->getFechaInicio() : $fi;
        $menorFecha = ($menorFechaReg < $fi) ? $menorFechaReg : $fi;
        
        //Cantidad de días 
        $intervalo = $ff->diff($menorFecha);
        $duración = $intervalo->format('%a') + 1;

        
        //Verificar que la duración de la actividad no exceda el periodo de intervención
        switch ($criterio->getTipoIntervencion()->getCodigo()) {
            case 'baja':
                $limite = 30;
                break;
            case 'media':
                $limite = 90;
                break;
            default:
                $limite = 180;
        }
        if ($duración > $limite){
            $result = false;
        }
        
        return $result;
    }
}
