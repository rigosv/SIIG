<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class IndicadorAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'codigo' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('codigo', null, array('label'=> $this->getTranslator()->trans('_codigo_')))
            ->add('descripcion', null, array('label'=> $this->getTranslator()->trans('_descripcion_')))
            ->add('estandar', null, array('label'=> $this->getTranslator()->trans('_estandar_')))
            ->add('formaEvaluacion', 'choice', array('label' => $this->getTranslator()->trans('_forma_evaluacion_'),
                        'choices' => array(
                            'cumplimiento_porcentaje_aceptacion'=>$this->getTranslator()->trans('_cumplimiento_porcentaje_aceptacion_'),
                            'cumplimiento_criterios'=>$this->getTranslator()->trans('_cumplimiento_criterios_'),
                            'promedio' => $this->getTranslator()->trans('_promedio_')
                            )
                        ))            
            ->add('porcentajeAceptacion', null, array('label'=> $this->getTranslator()->trans('_porcentaje_aceptacion_')))
            ->add('IndicadorPadre', null, array('label'=> $this->getTranslator()->trans('_indicador_padre_')))
            ->add('criterios', null, 
                    array('label'=> $this->getTranslator()->trans('_criterios_'), 
                        'expanded' => true, 
                        'multiple' => true,
                        'by_reference' => false,
                        'class' => 'GridFormBundle:VariableCaptura',
                            'query_builder' => function ($repository) {
                                return $repository->createQueryBuilder('c')
                                        ->orderBy('c.formulario, c.posicion');
                            }))
            
        ;
        $formMapper
            ->setHelps(array(
                'estandar' => $this->getTranslator()->trans('_indicador_estandar_help_')                
            ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('codigo', null, array('label'=> $this->getTranslator()->trans('_codigo_')))
            ->add('descripcion', null, array('label'=> $this->getTranslator()->trans('_descripcion_')))
            ->add('estandar', null, array('label'=> $this->getTranslator()->trans('_estardar_')))
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
}
