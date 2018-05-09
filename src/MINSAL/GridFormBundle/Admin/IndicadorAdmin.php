<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class IndicadorAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'codigo' // name of the ordered field (default = the model id field, if any)
    );
    
    public function getNewInstance()
    {
        $instance = parent::getNewInstance();
        $instance->setPonderaEstandar(true);

        return $instance;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $subject = $this->getSubject();
        $idIndicador = $subject->getId();
        $formMapper
            ->add('codigo', null, array('label'=> ('_codigo_')))
            ->add('descripcion', null, array('label'=> ('_descripcion_')))
            ->add('estandar', null, array('label'=> ('_estandar_')))
            ->add('dimension', null, array('label'=> ('_dimension_calidad_')))
            ->add('formaEvaluacion', 'choice', array('label' => ('_forma_evaluacion_'),
                        'choices' => array(
                            'cumplimiento_porcentaje_aceptacion'=>('_cumplimiento_porcentaje_aceptacion_'),
                            'cumplimiento_criterios'=>('_cumplimiento_criterios_'),
                            'promedio' => ('_promedio_')
                            )
                        ))
            ->add('porcentajeAceptacion', null, array('label'=> ('_porcentaje_aceptacion_')))
            ->add('unidadMedida', null, array('label'=> ('_unidad_medida_')))
            ->add('esTrazador', null, array('label'=> ('_es_trazador_')))
            ->add('ponderaEstandar', null, array('label'=> ('_pondera_estandar_')))
            ->add('posicion', null, array('label'=> ('_posicion_')))            
            ->add('criterios', null, 
                    array('label'=> ('_criterios_'), 
                        'expanded' => false,
                        'group_by'=> 'formulario',
                        'multiple' => true,
                        'by_reference' => false,
                        'class' => 'GridFormBundle:VariableCaptura',
                            'query_builder' => function ($repository) {
                                return $repository->createQueryBuilder('c')
                                        ->join('c.formulario', 'f')
                                        ->where("f.areaCosteo = 'calidad'")
                                        ->orderBy('f.posicion, c.posicion')
                                        ;
                            }))
            ->add('criteriosNoPonderados', EntityType::class, 
                    array('label'=> ('_criterios_no_ponderados_'), 
                        'expanded' => false,
                        'group_by'=> 'formulario',
                        'multiple' => true,
                        'by_reference' => false,
                        'class' => 'GridFormBundle:VariableCaptura',
                            'query_builder' => function ($repository) use ($idIndicador){
                                return $repository->createQueryBuilder('c')
                                        ->join('c.formulario', 'f')
                                        ->join('c.indicadores', 'i')
                                        ->where("f.areaCosteo = 'calidad'")
                                        ->andWhere("i.id = $idIndicador")
                                        ->orderBy('f.posicion, c.posicion')
                                        ;
                            }))
            ->add('alertas', 'entity', 
                    array('label'=> ('_alertas_'), 
                    'expanded' => false, 
                    'multiple' => true,
                    'by_reference' => false,
                    'class' => 'GridFormBundle:RangoAlerta',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                                ->orderBy('c.color, c.limiteInferior, c.limiteSuperior');
                    }    
                    ))
            
        ;
        $formMapper
            ->setHelps(array(
                'estandar' => ('_indicador_estandar_help_'),
                'esTrazador' => ('_es_trazador_help_'),
                'ponderaEstandar' => ('_pondera_estandar_help_'),
                'posicion' => ('_posicion_help_'),
                'criteriosNoPonderados' => '_criterios_no_ponderados_help_'
            ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('codigo', null, array('label'=> ('_codigo_')))
            ->add('descripcion', null, array('label'=> ('_descripcion_')))
            ->add('estandar', null, array('label'=> ('_estandar_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('codigo', null, array('label'=> ('_codigo_')))
            ->add('descripcion', null, array('label'=> ('_descripcion_'))) 
            ->add('estandar', null, array('label'=> ('_estandar_'))) 
            ->add('criterios', null, array('label'=> ('_criterios_'))) 
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);
        return $actions;
    }
    
    public function getTemplate($name)
    {
        switch ($name) {
            case 'edit':
                return 'GridFormBundle:CRUD:IndicadorAdmin-edit.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('tableroCalidad');
        $collection->add('tableroGeneralCalidad');

    }
    
    public function preUpdate($indicador) {
                
        $container = $this->getConfigurationPool()->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        
        $indicadorFrm = $this->getSubject();
        $criterios = $indicador->getCriteriosNoPonderados();
        
        if ($indicador->getCriteriosNoPonderados() != null ) {
            foreach ($indicador->getCriteriosNoPonderados()->getSnapshot() as $c ) {
                $indicador->removeCriteriosNoPonderado($c);
                $c->removeIndicadoresNoPonderar($indicador);
            }
        }
        
        //Agregar
        foreach ($indicadorFrm->getCriteriosNoPonderados() as $c ) {
            $c->addIndicadoresNoPonderar($indicador);
        }
        $em->persist($indicador);
        $em->flush();
    }
}
