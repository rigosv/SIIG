<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PlanIntervencionActividadAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'fechaCumplimiento' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        if (!$formMapper->getAdmin()->isChild()) {
            $formMapper            
                ->add('planIntervencion', null, array('label'=> $this->getTranslator()->trans('_plan_intervencion_')));
        }
        $formMapper
            ->add('fechaCumplimiento', 'sonata_type_date_picker', array('label'=> $this->getTranslator()->trans('_fecha_cumplimiento_')))
            ->add('descripcion', null, array('label'=> $this->getTranslator()->trans('_descripcion_')))
            ->add('responsable', null, array('label'=> $this->getTranslator()->trans('_responsable_')))
            ->add('fechaEvaluacion', 'sonata_type_date_picker', array('label'=> $this->getTranslator()->trans('_fecha_evaluacion_'), 'required' => false))
            ->add('resultadoEvaluacion', null, array('label'=> $this->getTranslator()->trans('_resultado_evaluacion_')))
            ->add('medidas', null, array('label'=> $this->getTranslator()->trans('_medidas_')))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        if (!$datagridMapper->getAdmin()->isChild()) {
            $datagridMapper
                ->add('planIntervencion', null, array('label'=> $this->getTranslator()->trans('_estandar_')))
            ;
        }
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('label'=> $this->getTranslator()->trans('_codigo_')));
        if (!$listMapper->getAdmin()->isChild()) {
            $listMapper->add('planIntervencion');
        }
        $listMapper
            ->add('fechaCumplimiento', null, array('label'=> $this->getTranslator()->trans('_fecha_cumplimiento_')))
            ->add('descripcion', null, array('label'=> $this->getTranslator()->trans('_descripcion_')))
            ->add('responsable', null, array('label'=> $this->getTranslator()->trans('_responsable_')))
            ->add('fechaEvaluacion', null, array('label'=> $this->getTranslator()->trans('_fecha_evaluacion_')))
            ->add('resultadoEvaluacion', null, array('label'=> $this->getTranslator()->trans('_resultado_evaluacion_')))
            ->add('medidas', null, array('label'=> $this->getTranslator()->trans('_medidas_')))
        ;
    }
    
    public function configure()
    {
        $this->parentAssociationMapping = 'planIntervencion';
    }
    
    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }
}
