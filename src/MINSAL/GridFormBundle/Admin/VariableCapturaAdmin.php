<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Doctrine\ORM\EntityRepository;

class VariableCapturaAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'descripcion' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper                
                ->add('codigo', null, array('label' => ('_codigo_')))
                ->add('area', null, array('label' => ('_area_')))
                ->add('descripcion', null, array('label' => ('_descripcion_')))
                ->add('formulario', null, array('label' => ('_formulario_'),'required' => true))
                ->add('versionFormulario', null, array('label' => ('_version_formulario_')))
                ->add('textoAyuda', null, array('label' => ('_ayuda_')))
                ->add('esPoblacion', null, array('label' => ('_es_poblacion_')))
                ->add('reglaValidacion', null, array('label' => ('_regla_validacion_')))
                ->add('mensajeValidacion', null, array('label' => ('_mensaje_validacion_')))
                ->add('formulaCalculo', null, array('label'=> ('_formula_campo_calculado_')))
                ->add('logicaSalto', null, array('label'=> ('_logica_salto_')))                
                ->add('tipoControl', null, array('label'=> ('_tipo_control_'), 'required' => true))
                ->add('origenFila', null, array('label'=> ('_origen_fila_'), 'required' => false))
                ->add('posicion', null, array('label'=> ('_posicion_'), 'required' => true))
                ->add('nivelIndentacion', null, array('label'=> ('_nivel_indentacion_')))
                ->add('esSeparador', null, array('label'=> ('_separador_')))
                ->add('categoria', 'entity', array('label' => ('_categoria_'),
                    'class' => 'GridFormBundle:CategoriaVariableCaptura',
                    'property' => 'descripcion',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                                ->orderBy('c.descripcion');
                    }
                ))
                ->add('alertas', 'entity', 
                    array('label'=> ('_alertas_'), 
                    'required' => false,
                    'expanded' => false, 
                    'multiple' => true,
                    'by_reference' => false,
                    'class' => 'GridFormBundle:RangoAlerta',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                                ->orderBy('c.color, c.limiteInferior, c.limiteSuperior');
                    }    
                    ))
        ;
        $formMapper
            ->setHelps(array(
                    'reglaValidacion' => ('_operadores_permitidos_'),
                    'formulaCalculo' => ('_formula_calculo_help_'),
                    'logicaSalto' => ('_logica_salto_help_'),
                    'origenFila' => ('_origen_fila_help_'),
                    'versionFormulario' => ('_version_formulario_')
                ))
                ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('codigo', null, array('label' => ('_codigo_')))
                ->add('area', null, array('label' => ('_area_')))
                ->add('descripcion', null, array('label' => ('_descripcion_')))
                ->add('formulario', null, array('label' => ('_formulario_')))
                ->add('categoria', null, array('label' => ('_categoria_')))
                ->add('versionFormulario', null, array('label' => ('_version_formulario_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('codigo', null, array('label' => ('_codigo_')))
                ->add('descripcion', null, array('label' => ('_descripcion_')))
                ->add('categoria', null, array('label' => ('_categoria_')))
                ->add('formulario', null, array('label' => ('_formulario_')))
                ->add('area', null, array('label' => ('_area_')))
                ->add('posicion', null, array('label'=> ('_posicion_')))
                ->add('alertas', null, array('label'=> ('_alertas_')))
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
                return 'GridFormBundle:CRUD:variableCaptura-edit.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

}
