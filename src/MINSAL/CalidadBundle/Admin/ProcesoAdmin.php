<?php

namespace MINSAL\CalidadBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ProcesoAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'codigo' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('codigo', null, array('label'=> ('_codigo_')))
            ->add('descripcion', null, array('label'=> ('_descripcion_')))
            ->add('dimension', null, array('label'=> ('_dimension_')))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('codigo', null, array('label'=> ('_codigo_')))
            ->add('descripcion', null, array('label'=> ('_descripcion_')))
            ->add('dimension', null, array('label'=> ('_dimension_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('codigo', null, array('label'=> ('_codigo_')))
            ->add('descripcion', null, array('label'=> ('_descripcion_')))
            ->add('dimension', null, array('label'=> ('_dimension_')))
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);
        return $actions;
    }
}
