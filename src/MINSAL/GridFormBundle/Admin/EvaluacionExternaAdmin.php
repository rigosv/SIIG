<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class EvaluacionExternaAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('establecimiento', null, array('label'=> $this->getTranslator()->trans('_establecimiento_')))
            ->add('tipoEvaluacion', null, array('label'=> $this->getTranslator()->trans('_tipo_evaluacion_')))
            ->add('anio', null, array('label'=> $this->getTranslator()->trans('_anio_')))
            ->add('valor', null, array('label'=> $this->getTranslator()->trans('_valor_')))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('establecimiento', null, array('label'=> $this->getTranslator()->trans('_establecimiento_')))
            ->add('tipoEvaluacion', null, array('label'=> $this->getTranslator()->trans('_tipo_evaluacion_')))
            ->add('anio', null, array('label'=> $this->getTranslator()->trans('_anio_')))
            ->add('valor', null, array('label'=> $this->getTranslator()->trans('_valor_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('label'=> $this->getTranslator()->trans('_id_')))
            ->add('establecimiento', null, array('label'=> $this->getTranslator()->trans('_establecimiento_')))
            ->add('tipoEvaluacion', null, array('label'=> $this->getTranslator()->trans('_tipo_evaluacion_')))
            ->add('anio', null, array('label'=> $this->getTranslator()->trans('_anio_')))
            ->add('valor', null, array('label'=> $this->getTranslator()->trans('_valor_')))
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }
}
