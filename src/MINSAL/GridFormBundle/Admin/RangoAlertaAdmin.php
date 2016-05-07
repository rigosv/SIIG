<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class RangoAlertaAdmin extends Admin {

    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'limiteInferior' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper) {       
        $formMapper
                ->add('limiteInferior', null, array('label' => $this->getTranslator()->trans('_alerta_limite_inferior_'),
                    'required' => true))
                ->add('limiteSuperior', null, array('label' => $this->getTranslator()->trans('limite_superior'),
                    'required' => true))
                ->add('color', 'choice', array('label' => $this->getTranslator()->trans('color'),
                        'choices' => array(
                            'green'=>$this->getTranslator()->trans('_green_'),
                            'yellow' => $this->getTranslator()->trans('_yellow_'),
                            'red' => $this->getTranslator()->trans('_red_')
                            ),
                         'required' => true   
                        ))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('limiteInferior', null, array('label' => $this->getTranslator()->trans('_alerta_limite_inferior_'),
                    'required' => true))
                ->add('limiteSuperior', null, array('label' => $this->getTranslator()->trans('limite_superior'),
                    'required' => true))
                ->add('color', null, array('label' => $this->getTranslator()->trans('color')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id', null, array('label' => $this->getTranslator()->trans('Id')))
                ->add('limiteInferior', null, array('label' => $this->getTranslator()->trans('_limite_inferior_')))
                ->add('limiteSuperior', null, array('label' => $this->getTranslator()->trans('limite_superior')))
                ->add('color', null, array('label' => $this->getTranslator()->trans('color')))

        ;
    }

    public function getBatchActions() {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }

}
