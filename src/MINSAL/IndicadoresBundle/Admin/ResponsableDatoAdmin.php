<?php

namespace MINSAL\IndicadoresBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ResponsableDatoAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'contacto' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('contacto', null, array('label'=> ('contacto')))
            ->add('establecimiento', null, array('label'=> ('establecimiento')))
            ->add('correo', 'email', array('label'=> ('correo_electronico')))
            ->add('telefono', null, array('label'=> ('telefono')))
            ->add('cargo', null, array('label'=> ('cargo')))
            ->setHelps(array(
                'telefono' => ('formato_xxxx-xxxx')
            ))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('contacto', null, array('label'=> ('contacto')))
            ->add('establecimiento',null, array('label'=> ('establecimiento')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('contacto', null, array('label'=> ('contacto')))
            ->add('establecimiento', null, array('label'=> ('nombre_establecimiento')))
            ->add('correo', null, array('label'=> ('correo_electronico')))
            ->add('telefono', null, array('label'=> ('telefono')))
            ->add('cargo', null, array('label'=> ('cargo')))

        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }
}
