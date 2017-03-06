<?php

namespace MINSAL\CalidadBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PlanMejoraAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('estandar', null, array('label'=> ('_estandar_')))
            ->add('establecimiento', null, array('label'=> ('_establecimiento_')))
            ->add('periodo', null, array('label'=> ('_periodo_')))            
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('estandar', null, array('label'=> ('_estandar_')))
            ->add('establecimiento', null, array('label'=> ('_establecimiento_')))
            ->add('periodo', null, array('label'=> ('_periodo_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('label'=> ('_id_')))
            ->add('estandar', null, array('label'=> ('_estandar_')))
            ->add('establecimiento', null, array('label'=> ('_establecimiento_')))
            ->add('periodo', null, array('label'=> ('_periodo_')))            
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }
}
