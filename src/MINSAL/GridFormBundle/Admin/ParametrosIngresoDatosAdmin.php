<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
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
            ->add('establecimiento', null, array('label'=> ('_establecimiento_')))
            ->add('formulario', null, array('label'=> ('_estandar_')))
            ->add('fechaEvaluacion', null, array('label'=> ('_fecha_evaluacion_')))
            ->add('anio', null, array('label'=> ('_anio_')))
            ->add('mes', null, array('label'=> ('_mes_')))
            ->add('nombreResponsable', null, array('label'=> ('_nombre_responsable_evaluacion_')))
            ->add('cantidadExpedienteReportar', null, array('label'=> ('_cantidad_expedientes_reportar_')))
            ->add('observaciones', null, array('label'=> ('_observaciones_')))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('establecimiento', null, array('label'=> ('_establecimiento_')))
            ->add('formulario', null, array('label'=> ('_estandar_')))            
            ->add('anio', null, array('label'=> ('_anio_')))
            ->add('mes', null, array('label'=> ('_mes_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('label'=> ('_codigo_')))
            ->add('establecimiento', null, array('label'=> ('_establecimiento_')))
            ->add('formulario', null, array('label'=> ('_estandar_')))
            ->add('fechaEvaluacion', null, array('label'=> ('_fecha_evaluacion_')))
            ->add('anio', null, array('label'=> ('_anio_')))
            ->add('mes', null, array('label'=> ('_mes_')))
            ->add('nombreResponsable', null, array('label'=> ('_nombre_responsable_evaluacion_'))) 
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }
}
