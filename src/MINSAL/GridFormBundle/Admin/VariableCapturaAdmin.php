<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
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
                ->add('codigo', null, array('label' => $this->getTranslator()->trans('_codigo_')))
                ->add('descripcion', null, array('label' => $this->getTranslator()->trans('_descripcion_')))
                ->add('formulario', null, array('label' => $this->getTranslator()->trans('_formulario_')))
                ->add('textoAyuda', null, array('label' => $this->getTranslator()->trans('_ayuda_')))
                ->add('esPoblacion', null, array('label' => $this->getTranslator()->trans('_es_poblacion_')))
                ->add('reglaValidacion', null, array('label' => $this->getTranslator()->trans('_regla_validacion_')))
                ->add('tipoControl', null, array('label'=> $this->getTranslator()->trans('_tipo_control_')))
                ->add('posicion', null, array('label'=> $this->getTranslator()->trans('_posicion_')))
                ->add('nivelIndentacion', null, array('label'=> $this->getTranslator()->trans('_nivel_indentacion_')))
                ->add('esSeparador', null, array('label'=> $this->getTranslator()->trans('_separador_')))
                ->add('categoria', 'entity', array('label' => $this->getTranslator()->trans('_categoria_'),
                    'class' => 'GridFormBundle:CategoriaVariableCaptura',
                    'property' => 'descripcion',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('c')
                                ->orderBy('c.descripcion');
                    }
                ))
                ->add('origenFilas', null, array('label' => $this->getTranslator()->trans('_origen_filas_')))
                ->add('alertas', null, 
                    array('label'=> $this->getTranslator()->trans('_alertas_'), 
                    'expanded' => false, 
                    'multiple' => true,
                    'by_reference' => false
                    ))
        ;
        $formMapper
            ->setHelps(array(
                    'reglaValidacion' => $this->getTranslator()->trans('_operadores_permitidos_'),
                    'origenFilas' => $this->getTranslator()->trans('_origen_filas_help_')
                ))
                ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('codigo', null, array('label' => $this->getTranslator()->trans('_codigo_')))
                ->add('descripcion', null, array('label' => $this->getTranslator()->trans('_descripcion_')))
                ->add('formulario', null, array('label' => $this->getTranslator()->trans('_formulario_')))
                ->add('categoria', null, array('label' => $this->getTranslator()->trans('_categoria_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('codigo', null, array('label' => $this->getTranslator()->trans('_codigo_')))
                ->add('descripcion', null, array('label' => $this->getTranslator()->trans('_descripcion_')))
                ->add('categoria', null, array('label' => $this->getTranslator()->trans('_categoria_')))
                ->add('formulario', null, array('label' => $this->getTranslator()->trans('_formulario_')))
                ->add('posicion', null, array('label'=> $this->getTranslator()->trans('_posicion_')))
        ;
    }    

    public function validate(ErrorElement $errorElement, $object)
    {
        
            // ******** Verificar si matematicamente la regla de validacion es correcta
            // 1) Sustituir value por un valor fijo
            // Quitar las palabras permitidas
            $regla_check = str_replace(array('value',' and ', ' or '), array(20, ' && ', ' || '), 
                    $object->getReglaValidacion());

            $regla_valida = true;
            $result = true;
            
            //Verificar que no tenga letras, para evitar un ataque de inyección
            if (preg_match('/[A-Z]+/i', $regla_check) != 0) {
                $regla_valida = false;
                $mensaje = 'sintaxis_invalida';
            } else {
                //evaluar la formula, evitar que se muestren los errores por si los lleva
                ob_start();
                $test = eval('$result=' . $regla_check . ';');
                ob_end_clean();

                if (!$result){                
                    $regla_valida = false;
                    $mensaje = 'sintaxis_invalida';
                }
            }

            if ($regla_valida == false) {
                $errorElement
                        ->with('reglaValidacion')
                        ->addViolation($this->getTranslator()->trans($mensaje))
                        ->end();
            }
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
                return 'GridFormBundle:CRUD:variableCaptura-edit.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

}
