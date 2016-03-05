<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PeriodoIngresoDatosFormularioAdmin extends Admin
{    

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('usuario', null, array('label'=> $this->getTranslator()->trans('_usuario_')))
            ->add('formulario', null, array('label'=> $this->getTranslator()->trans('_formulario_')))
            ->add('periodo', null, array('label'=> $this->getTranslator()->trans('_periodo_ingreso_')))
            ->add('unidad', null, array('label'=> $this->getTranslator()->trans('_unidad_')))            
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('usuario', null, array('label'=> $this->getTranslator()->trans('_usuario_')))
            ->add('formulario', null, array('label'=> $this->getTranslator()->trans('_formulario_')))
            ->add('unidad', null, array('label'=> $this->getTranslator()->trans('_unidad_')))
            ->add('periodo', null, array('label'=> $this->getTranslator()->trans('_periodo_ingreso_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper            
            ->addIdentifier('id', null, array('label'=> $this->getTranslator()->trans('_id_')))
            ->add('usuario', null, array('label'=> $this->getTranslator()->trans('_usuario_')))
            ->add('formulario', null, array('label'=> $this->getTranslator()->trans('_formulario_')))
            ->add('unidad', null, array('label'=> $this->getTranslator()->trans('_unidad_')))
            ->add('periodo', null, array('label'=> $this->getTranslator()->trans('_periodo_ingreso_')))
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
                return 'GridFormBundle:CRUD:periodoIngresoDatos-edit.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }
}