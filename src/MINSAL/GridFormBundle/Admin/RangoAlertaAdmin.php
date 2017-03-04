<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;

class RangoAlertaAdmin extends Admin {

    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'limiteInferior' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper) {       
        $formMapper
                ->add('limiteInferior', null, array('label' => ('_alerta_limite_inferior_')))
                ->add('limiteSuperior', null, array('label' => ('limite_superior')))
                ->add('color', 'choice', array('label' => ('color'),
                        'choices' => array(
                            'green'=>('_green_'),
                            'orange' => ('_orange_'),
                            'red' => ('_red_')
                            ),
                         'required' => true   
                        ))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('limiteInferior', null, array('label' => ('_alerta_limite_inferior_'),
                    'required' => true))
                ->add('limiteSuperior', null, array('label' => ('limite_superior'),
                    'required' => true))
                ->add('color', null, array('label' => ('color')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id', null, array('label' => ('Id')))
                ->add('limiteInferior', null, array('label' => ('_limite_inferior_')))
                ->add('limiteSuperior', null, array('label' => ('limite_superior')))
                ->add('color', null, array('label' => ('color')))

        ;
    }

    public function getBatchActions() {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }
    
    public function validate(ErrorElement $errorElement, $object)
    {
        if ($object->getLimiteInferior() == "" and $object->getLimiteSuperior() == ""){
            $errorElement
                    ->with('limiteInferior')
                    ->addViolation(('_ambos_limites_vacios_'))
                    ->end();
                return;
        }
    }
}
