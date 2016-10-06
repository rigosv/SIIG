<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Doctrine\ORM\EntityRepository;

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
        $formMapper
            ->add('codigo', null, array('label'=> $this->getTranslator()->trans('_codigo_')))
            ->add('descripcion', null, array('label'=> $this->getTranslator()->trans('_descripcion_')))
            ->add('estandar', null, array('label'=> $this->getTranslator()->trans('_estandar_')))
            ->add('dimension', null, array('label'=> $this->getTranslator()->trans('_dimension_calidad_')))
            ->add('formaEvaluacion', 'choice', array('label' => $this->getTranslator()->trans('_forma_evaluacion_'),
                        'choices' => array(
                            'cumplimiento_porcentaje_aceptacion'=>$this->getTranslator()->trans('_cumplimiento_porcentaje_aceptacion_'),
                            'cumplimiento_criterios'=>$this->getTranslator()->trans('_cumplimiento_criterios_'),
                            'promedio' => $this->getTranslator()->trans('_promedio_')
                            )
                        ))
            ->add('porcentajeAceptacion', null, array('label'=> $this->getTranslator()->trans('_porcentaje_aceptacion_')))
            ->add('unidadMedida', null, array('label'=> $this->getTranslator()->trans('_unidad_medida_')))
            ->add('esTrazador', null, array('label'=> $this->getTranslator()->trans('_es_trazador_')))
            ->add('ponderaEstandar', null, array('label'=> $this->getTranslator()->trans('_pondera_estandar_')))
            ->add('posicion', null, array('label'=> $this->getTranslator()->trans('_posicion_')))            
            ->add('criterios', null, 
                    array('label'=> $this->getTranslator()->trans('_criterios_'), 
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
            ->add('alertas', 'entity', 
                    array('label'=> $this->getTranslator()->trans('_alertas_'), 
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
                'estandar' => $this->getTranslator()->trans('_indicador_estandar_help_'),
                'esTrazador' => $this->getTranslator()->trans('_es_trazador_help_'),
                'ponderaEstandar' => $this->getTranslator()->trans('_pondera_estandar_help_'),
                'posicion' => $this->getTranslator()->trans('_posicion_help_')
            ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('codigo', null, array('label'=> $this->getTranslator()->trans('_codigo_')))
            ->add('descripcion', null, array('label'=> $this->getTranslator()->trans('_descripcion_')))
            ->add('estandar', null, array('label'=> $this->getTranslator()->trans('_estandar_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('codigo', null, array('label'=> $this->getTranslator()->trans('_codigo_')))
            ->add('descripcion', null, array('label'=> $this->getTranslator()->trans('_descripcion_'))) 
            ->add('estandar', null, array('label'=> $this->getTranslator()->trans('_estandar_'))) 
            ->add('criterios', null, array('label'=> $this->getTranslator()->trans('_criterios_'))) 
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
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
}
