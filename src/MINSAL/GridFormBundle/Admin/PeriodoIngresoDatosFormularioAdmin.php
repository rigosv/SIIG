<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PeriodoIngresoDatosFormularioAdmin extends Admin
{    

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('usuario', null, array('label'=> ('_usuario_')))
            ->add('formulario', null, array('label'=> ('_formulario_')))
            ->add('periodo', null, array('label'=> ('_periodo_ingreso_')))
            ->add('unidad', null, array('label'=> ('_unidad_')))            
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('usuario', null, array('label'=> ('_usuario_')))
            ->add('formulario', null, array('label'=> ('_formulario_')))
            ->add('unidad', null, array('label'=> ('_unidad_')))
            ->add('periodo', null, array('label'=> ('_periodo_ingreso_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper            
            ->addIdentifier('id', null, array('label'=> ('_id_')))
            ->add('usuario', null, array('label'=> ('_usuario_')))
            ->add('formulario', null, array('label'=> ('_formulario_')))
            ->add('unidad', null, array('label'=> ('_unidad_')))
            ->add('periodo', null, array('label'=> ('_periodo_ingreso_')))
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
                return 'GridFormBundle:CRUD:periodoIngresoDatos-edit.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }
}
