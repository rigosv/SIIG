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
            ->add('establecimiento', null, array('label'=> ('_establecimiento_')))
            ->add('tipoEvaluacion', null, array('label'=> ('_tipo_evaluacion_')))
            ->add('anio', null, array('label'=> ('_anio_')))
            ->add('valor', null, array('label'=> ('_valor_')))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('establecimiento', null, array('label'=> ('_establecimiento_')))
            ->add('tipoEvaluacion', null, array('label'=> ('_tipo_evaluacion_')))
            ->add('anio', null, array('label'=> ('_anio_')))
            ->add('valor', null, array('label'=> ('_valor_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('label'=> ('_id_')))
            ->add('establecimiento', null, array('label'=> ('_establecimiento_')))
            ->add('tipoEvaluacion', null, array('label'=> ('_tipo_evaluacion_')))
            ->add('anio', null, array('label'=> ('_anio_')))
            ->add('valor', null, array('label'=> ('_valor_')))
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);
        return $actions;
    }
}
