<?php

namespace MINSAL\IndicadoresBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use MINSAL\IndicadoresBundle\Entity\FichaTecnica;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

class FichaTecnicaAdmin extends Admin
{
    private $repository;
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'nombre' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->tab(('ficha_tecnica'))
                ->with(('_datos_generales_'), array('class' => 'col-md-8'))->end()
                ->with(('_clasificacion_'), array('class' => 'col-md-4'))->end()
            ->end()
            ->tab(('_configuracion_'))
                ->with(('_dimensiones_'), array('class' => 'col-md-4'))->end()
                ->with(('alertas'), array('class' => 'col-md-8'))->end()                
            ->end()
        ;
        $formMapper
                ->tab(('ficha_tecnica'))
                    ->with(('_datos_generales_'))
                        ->add('codigo', null, array('label' => ('codigo'), 'required' => false))
                        ->add('nombre', null, array('label' => ('nombre_indicador')))
                        ->add('tema', null, array('label' => ('_interpretacion_')))
                        ->add('concepto', null, array('label' => ('concepto')))
                        ->add('unidadMedida', null, array('label' => ('unidad_medida')))
                        ->add('esAcumulado', null, array('label' => ('es_acumulado')))
                        ->add('ruta', null, array('label' => ('_ruta_')))
                        ->add('variables', null, array('label' => ('variables'), 'expanded' => false,
                            'class' => 'IndicadoresBundle:VariableDato',
                            'query_builder' => function ($repository) {
                                return $repository->createQueryBuilder('vd')
                                        ->orderBy('vd.nombre');
                            }))
                        ->add('formula', null, array('label' => ('formula'),
                            'help' => ('ayuda_ingreso_formula')
                        ))
                        ->add('meta', null, array('label' => ('_meta_')))
                        ->add('periodo', null, array('label' => ('periodicidad')))
                        ->add('confiabilidad', null, array('label' => ('confiabilidad'), 'required' => false))
                        ->add('reporte', null, array('label' => ('_reporte_'), 'required' => false))
                        ->add('observacion', 'textarea', array('label' => ('_observacion_'), 'required' => false))
                    ->end()
                    ->with(('_clasificacion_'))
                        ->add('clasificacionTecnica', null, array('label' => ('clasificacion_tecnica'),
                            'required' => true, 'expanded' => true,
                            'class' => 'IndicadoresBundle:ClasificacionTecnica',
                            'query_builder' => function ($repository) {
                                return $repository->createQueryBuilder('ct')
                                        ->orderBy('ct.clasificacionUso');
                            }))
                        ->add('clasificacionPrivacidad', null, array('label' => ('_nivel_de_usuario_'), 'expanded' => true))                
                    ->end()
                ->end()
                ->tab(('_configuracion_'))
                    ->with(('alertas'))
                        ->add('alertas', 'sonata_type_collection', array(
                            'label' => ('alertas'),
                            'required' => true), array(
                            'edit' => 'inline',
                            'inline' => 'table',
                            'sortable' => 'position'
                        ))
                    ->end()
                    ->with(('_dimensiones_'))
                        ->add('camposIndicador', null, array('label' => ('campos_indicador')))
                    ->end()
                ->end()
                ;
        $acciones = explode('/', $this->getRequest()->server->get("REQUEST_URI"));
        $accion = array_pop($acciones);
        if ($accion == 'create') {
            $formMapper
                    ->setHelps(array(
                        'camposIndicador' => ('_debe_guardar_para_ver_dimensiones_')
                    ))
            ;
        }
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
                ->add('codigo', null, array('label' => ('codigo')))
                ->add('tema', null, array('label' => ('_interpretacion_')))
                ->add('concepto', null, array('label' => ('concepto')))
                ->add('unidadMedida', null, array('label' => ('unidad_medida')))
                ->add('esAcumulado', null, array('label' => ('es_acumulado')))
                ->add('variables', null, array('label' => ('variables'), 'expanded' => true))
                ->add('formula', null, array('label' => ('formula')))
                ->add('clasificacionTecnica', null, array('label' => ('clasificacion_tecnica'),
                    'required' => true, 'expanded' => true,
                    'class' => 'IndicadoresBundle:ClasificacionTecnica',
                    'query_builder' => function ($repository) {
                        return $repository->createQueryBuilder('ct')
                                ->orderBy('ct.clasificacionUso');
                    }))
                ->add('clasificacionPrivacidad', null, array('label' => ('_nivel_de_usuario_'), 'expanded' => true))
                ->add('periodo', null, array('label' => ('periodicidad')))
                ->add('confiabilidad', null, array('label' => ('confiabilidad')))
                ->add('observacion', 'string', array('label' => ('_observacion_')))
                ->add('alertas', 'sonata_type_collection', array(
                    'label' => ('alertas'),
                    'required' => true), array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'position'
                ))
                ->add('camposIndicador', null, array('label' => ('campos_indicador')))
                ->add('ultimaLectura', null, array('label' => ('_ultima_actualizacion_')))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('codigo', null, array('label' => ('codigo')))
                ->add('nombre', null, array('label' => ('nombre')))
                ->add('clasificacionTecnica', null, array('label' => ('clasificacion_tecnica')))
                ->add('clasificacionPrivacidad', null, array('label' => ('_nivel_de_usuario_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('codigo', null, array('label' => ('codigo')))
                ->addIdentifier('nombre', null, array('label' => ('nombre_indicador')))
                ->add('tema', null, array('label' => ('_interpretacion_')))
                ->add('concepto', null, array('label' => ('concepto')))
                ->add('camposIndicador', null, array('label' => ('campos_indicador')))
                ->add('_action', 'actions', array(
                'actions' => array('reporte' => array('template' => 'IndicadoresBundle:FichaTecnicaAdmin:accion_reporte.html.twig'),
                        )
               )) ;

        ;
    }
    
    // Para que siempre muestre la opción del menú principal donde se escuentra la ficha
    public function showIn($context) {
        return true;
    }

    public function getBatchActions()
    {
        $actions = array();

        $actions['ver_ficha'] = array(
            'label' => $this->trans('_ver_ficha_'),
            'ask_confirmation' => false // If true, a confirmation will be asked before performing the action
        );

        return $actions;
    }

    public function validate(ErrorElement $errorElement, $object)
    {
        //Verificar que el usuario tiene una agencia asignada
        $usuario = $this->getConfigurationPool()
                ->getContainer()
                ->get('security.token_storage')
                ->getToken()
                ->getUser();
        if ($usuario->getAgencia() == null){
            $errorElement
                        ->with('nombre')
                        ->addViolation(('_usuario_no_agencia_'))
                        ->end();
        }

        //Verificar que todos los campos esten configurados
        foreach ($object->getVariables() as $variable) {
            $campos_no_configurados = $this->getModelManager()
                    ->findBy('IndicadoresBundle:Campo', array('origenDato' => $variable->getOrigenDatos(),
                'significado' => null));

            if (count($campos_no_configurados) > 0) {
                $errorElement
                        ->with('variables')
                        ->addViolation($variable->getIniciales() . ': ' . ('origen_no_configurado'))
                        ->end();
            }
        }
        //Obtener las variables marcadas
        $variables_sel = array();
        foreach ($object->getVariables() as $variable) {
            $variables_sel[] = $variable->getIniciales();
        }

        if (count($variables_sel) == 0)
            $errorElement
                    ->with('variables')
                    ->addViolation(('elija_al_menos_una_variable'))
                    ->end()
            ;
        else {
            //Obtener las variables utilizadas en la fórmula
            //Quitar todos los espacios en blanco de la fórmula
            $vars_formula = array();
            $formula = str_replace(' ', '', $object->getFormula());
            preg_match_all('/\{([\w]+)\}/', $formula, $vars_formula);

            //Para que la fórmula sea válida la cantidad de variables seleccionadas
            //debe coincidir con las utilizadas en la fórmula
            if ((count(array_diff($variables_sel, $vars_formula[1])) > 0) or
                    (count(array_diff($vars_formula[1], $variables_sel)) > 0)) {
                $errorElement
                        ->with('formula')
                        ->addViolation(('vars_sel_diff_vars_formula'))
                        ->end()
                ;
            }

            // ******** Verificar si matematicamente la fórmula es correcta
            // 1) Sustituir las variables por valores aleatorios entre 1 y 100
            // Quitar las palabras permitidas
            $formula_check = str_replace(
                    array('AVG', 'MAX', 'MIN', 'SUM', 'COUNT'), 
                    array('', '', '', '', ''), 
                    strtoupper($object->getFormula())
                    );
            $formula_valida = true;
            $result = '';
            foreach ($vars_formula[0] as $var) {
                $formula_check = str_replace($var, rand(1, 100), $formula_check);
            }

            //Verificar que no tenga letras, para evitar un ataque de inyección
            if (preg_match('/[A-Z]+/i', $formula_check) != 0) {
                $formula_valida = false;
                $mensaje = 'sintaxis_invalida_variables_entre_llaves';
            } else {
                //evaluar la formula, evitar que se muestren los errores por si los lleva
                ob_start();
                $test = eval('$result=' . $formula_check . ';');
                ob_end_clean();

                if (!is_numeric($result)) {
                    $formula_valida = false;
                    $mensaje = 'sintaxis_invalida';
                }
            }

            if ($formula_valida == false) {
                $errorElement
                        ->with('formula')
                        ->addViolation(($mensaje))
                        ->end();
            }
        }
    }

    public function postPersist($fichaTecnica)
    {
        $this->crearCamposIndicador($fichaTecnica);
        //$this->repository->crearTablaIndicador()
        $fichaTecnica->setUltimaLectura(new \DateTime("now"));
        
    }

    public function postUpdate($fichaTecnica)
    {
        $this->crearCamposIndicador($fichaTecnica);
        //$this->repository->crearTablaIndicador($fichaTecnica);
        $fichaTecnica->setUltimaLectura(new \DateTime("now"));
    }

    public function prePersist($fichaTecnica)
    {
        $this->setAlertas($fichaTecnica);
        $this->crearCamposIndicador($fichaTecnica);   
        
        /*
         * La agencia del indicador será la agencia del usuario que lo crea
         */
        $usuario = $this->getConfigurationPool()
                ->getContainer()
                ->get('security.token_storage')
                ->getToken()
                ->getUser();
        $fichaTecnica->setAgencia($usuario->getAgencia());
    }

    public function setAlertas($fichaTecnica)
    {
        $alertas = $fichaTecnica->getAlertas();
        $fichaTecnica->removeAlertas();
        if (count($alertas) > 0) {
            foreach ($alertas as $alerta) {
                $alerta->setIndicador($fichaTecnica);
                $fichaTecnica->addAlerta($alerta);
            }
        }
    }

    public function preUpdate($fichaTecnica)
    {
        $this->setAlertas($fichaTecnica);       
        $this->crearCamposIndicador($fichaTecnica);
    }

    public function crearCamposIndicador(FichaTecnica $fichaTecnica)
    {
        $em = $this->getConfigurationPool()->getContainer()->get('doctrine')->getManager();
        $em->getRepository('IndicadoresBundle:FichaTecnica')->crearCamposIndicador($fichaTecnica);
        
    }

    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('tablero');
        $collection->add('pivotTable');
        $collection->add('matrizSeguimiento');
    }

    public function getTemplate($name)
    {
        switch ($name) {
            case 'edit':
                return 'IndicadoresBundle:CRUD:ficha_tecnica-edit.html.twig';
                break;
            case 'show':
                return 'IndicadoresBundle:FichaTecnicaAdmin:show.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

    /**
     * Cambiar la forma en que muestra el listado de indicadores, 
     * si es un usuario normal solo le muestra los indicadores que tenga asignados
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        $usuario = $this->getConfigurationPool()
                ->getContainer()
                ->get('security.token_storage')
                ->getToken()
                ->getUser();
        if ($usuario->hasRole('ROLE_SUPER_ADMIN')) {
            return new ProxyQuery($query->where('1=1'));
        } else {
            $ind = $usuario->getIndicadores();
            $indicadores = array();
            foreach ($ind as $f) {
                $indicadores[] = $f->getId();
            }

            return new ProxyQuery(
                    $query->where($query->getRootAlias() . '.id IN ('.  implode(", ", $indicadores).')')
            );
        }
    }

}
