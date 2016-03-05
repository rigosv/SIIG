<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PeriodoIngresoGrupoUsuariosAdmin extends Admin
{    

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('grupoUsuario', null, array('label'=> $this->getTranslator()->trans('_grupo_usuarios_')))
            ->add('formulario', null, array('label'=> $this->getTranslator()->trans('_formulario_')))
            ->add('periodo', null, array('label'=> $this->getTranslator()->trans('_periodo_ingreso_')))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('grupoUsuario', null, array('label'=> $this->getTranslator()->trans('_grupo_usuario_')))
            ->add('formulario', null, array('label'=> $this->getTranslator()->trans('_formulario_')))
            ->add('periodo', null, array('label'=> $this->getTranslator()->trans('_periodo_ingreso_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper            
            ->addIdentifier('id', null, array('label'=> $this->getTranslator()->trans('_id_')))
            ->add('grupoUsuario', null, array('label'=> $this->getTranslator()->trans('_grupo_usuarios_')))
            ->add('formulario', null, array('label'=> $this->getTranslator()->trans('_formulario_')))
            ->add('periodo', null, array('label'=> $this->getTranslator()->trans('_periodo_ingreso_')))
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }
        
}
