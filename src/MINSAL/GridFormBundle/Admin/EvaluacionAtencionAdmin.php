<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class EvaluacionAtencionAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'formulario' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('formulario', null, array('label'=> $this->getTranslator()->trans('_formulario_')))
            ->add('establecimiento', null, array('label'=> $this->getTranslator()->trans('_establecimiento_')))
            ->add('areasAtencion', null, array('label'=> $this->getTranslator()->trans('_areas_atencion_')))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('formulario', null, array('label'=> $this->getTranslator()->trans('_formulario_')))
            ->add('establecimiento', null, array('label'=> $this->getTranslator()->trans('_establecimiento_')))
            ->add('areasAtencion', null, array('label'=> $this->getTranslator()->trans('_areas_atencion_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('label'=> $this->getTranslator()->trans('_id_')))
            ->add('formulario', null, array('label'=> $this->getTranslator()->trans('_formulario_')))
            ->add('establecimiento', null, array('label'=> $this->getTranslator()->trans('_establecimiento_')))
            ->add('areasAtencion', null, array('label'=> $this->getTranslator()->trans('_areas_atencion_')))
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }
}
