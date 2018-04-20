<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class CampoAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'codigo' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('significadoCampo', null, array('label'=> ('_significado_campo_')))
            ->add('descripcion', null, array('label'=> ('_descripcion_')))
            ->add('formularios', null, array('label'=> ('_formulario_'), 'by_reference' => false))
            ->add('reglaValidacion', null, array('label'=> ('_regla_validacion_')))
            ->add('msjValidacion', null, array('label'=> ('_msj_validacion_')))
            ->add('posicion', null, array('label'=> ('_posicion_')))
            ->add('ancho', null, array('label'=> ('_ancho_')))
            ->add('esEditable', null, array('label'=> ('_es_editable_')))
            ->add('oculto', null, array('label'=> ('_oculto_')))
            ->add('tipoDato', null, array('label'=> ('_tipo_dato_')))
            ->add('tipoControl', null, array('label'=> ('_tipo_control_')))
            ->add('alineacion', null, array('label'=> ('_alineacion_')))
            ->add('formato', null, array('label'=> ('_formato_')))
            ->add('origen', null, array('label'=> ('_origen_campo_')))
            ->add('grupoColumnas', null, array('label'=> ('_grupo_columnas_')))
            ->add('origenPivote', null, array('label'=> ('_origen_pivote_')))            
            ->setHelps(array(
                'formula' => ('_ayuda_formula_campo_'),
                'reglaValidacion' => ('_ayuda_validacion_campo_'),
                'origen' => ('_ayuda_origen_campo_'),
                'origenPivote' => ('_ayuda_origen_pivote_')
            ))
            
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('formularios', null, array('label'=> ('_formulario_')))
            ->add('significadoCampo', null, array('label'=> ('_significado_campo_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper            
            ->add('significadoCampo', null, array('label'=> ('_significado_campo_')))
            ->addIdentifier('descripcion', null, array('label'=> ('_descripcion_'))) 
            ->add('posicion', null, array('label'=> ('_posicion_')))
            ->add('formularios', null, array('label'=> ('_formulario_')))
            ->add('_action', 'actions', array(
            'actions' => array(
                'edit' => array(),
                'delete' => array(),
            )))
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);
        return $actions;
    }
}
