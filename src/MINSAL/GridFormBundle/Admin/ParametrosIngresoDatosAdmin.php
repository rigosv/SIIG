<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class ParametrosIngresoDatosAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')        
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('establecimiento', null, array('label'=> $this->getTranslator()->trans('_establecimiento_')))
            ->add('formulario', null, array('label'=> $this->getTranslator()->trans('_estandar_')))
            ->add('fechaEvaluacion', null, array('label'=> $this->getTranslator()->trans('_fecha_evaluacion_')))
            ->add('anio', null, array('label'=> $this->getTranslator()->trans('_anio_')))
            ->add('mes', null, array('label'=> $this->getTranslator()->trans('_mes_')))
            ->add('nombreResponsable', null, array('label'=> $this->getTranslator()->trans('_nombre_responsable_evaluacion_')))
            ->add('observaciones', null, array('label'=> $this->getTranslator()->trans('_observaciones_')))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('establecimiento', null, array('label'=> $this->getTranslator()->trans('_establecimiento_')))
            ->add('formulario', null, array('label'=> $this->getTranslator()->trans('_estandar_')))            
            ->add('anio', null, array('label'=> $this->getTranslator()->trans('_anio_')))
            ->add('mes', null, array('label'=> $this->getTranslator()->trans('_mes_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('label'=> $this->getTranslator()->trans('_codigo_')))
            ->add('establecimiento', null, array('label'=> $this->getTranslator()->trans('_establecimiento_')))
            ->add('formulario', null, array('label'=> $this->getTranslator()->trans('_estandar_')))
            ->add('fechaEvaluacion', null, array('label'=> $this->getTranslator()->trans('_fecha_evaluacion_')))
            ->add('anio', null, array('label'=> $this->getTranslator()->trans('_anio_')))
            ->add('mes', null, array('label'=> $this->getTranslator()->trans('_mes_')))
            ->add('nombreResponsable', null, array('label'=> $this->getTranslator()->trans('_nombre_responsable_evaluacion_'))) 
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }
}
