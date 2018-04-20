<?php

namespace MINSAL\CalidadBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class EstandarAdmin extends Admin
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
            ->add('nombre', null, array('label'=> ('_nombre_')))
            ->add('descripcion', null, array('label'=> ('_descripcion_')))
            ->add('formularioCaptura', null, array('label'=> ('_formulario_captura_datos_'), 'required' => true))
            ->add('posicion', null, array('label'=> ('_posicion_listados_')))
            ->add('proceso', null, array('label'=> ('_proceso_')))
            ->add('meta', null, array('label'=> ('_umbral_estandar_')))
            ->add('formaEvaluacion', 'choice', array('label' => ('_forma_evaluacion_'),
                        'choices' => array(
                            'lista_chequeo'=>('_lista_chequeo_'),
                            'rango_colores' => ('_rango_colores_')
                            )
                        ))
            ->add('evaluacionPorExpedientes', null, array('label'=> ('_evaluacion_por_expedientes_')))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('codigo', null, array('label'=> ('_codigo_')))
            ->add('nombre', null, array('label'=> ('_nombre_')))
            ->add('formularioCaptura', null, array('label'=> ('_formulario_captura_datos_')))
            ->add('proceso', null, array('label'=> ('_proceso_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('codigo', null, array('label'=> ('_codigo_')))
            ->add('nombre', null, array('label'=> ('_nombre_')))
            ->add('descripcion', null, array('label'=> ('_descripcion_')))
            ->add('formularioCaptura', null, array('label'=> ('_formulario_captura_datos_')))
            ->add('proceso', null, array('label'=> ('_proceso_')))
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);
        return $actions;
    }
}
