<?php

namespace MINSAL\IndicadoresBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class IndicadorAlertasAdmin extends Admin {

    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'color' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper) {
        
        if($this->getRequest()->get('_sonata_admin') == 'sonata.admin.indicador_alertas') {
            $formMapper
                ->add('indicador', null, array('label' => ('indicador')));
        }
        $formMapper
                ->add('limiteInferior', null, array('label' => ('_alerta_limite_inferior_'),
                    'required' => true))
                ->add('limiteSuperior', null, array('label' => ('limite_superior'),
                    'required' => true))
                ->add('color', null, array('label' => ('color'),
                    'required' => true))
                ->add('comentario', null, array('label' => ('comentario')))

        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper) {
        $datagridMapper
                ->add('indicador', null, array('label' => ('indicador')))
                ->add('color', null, array('label' => ('color')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper) {
        $listMapper
                ->addIdentifier('id', null, array('label' => ('Id')))
                ->add('indicador', null, array('label' => ('indicador')))
                ->add('limiteInferior', null, array('label' => ('limite_inferior')))
                ->add('limiteSuperior', null, array('label' => ('limite_superior')))
                ->add('color', null, array('label' => ('color')))
                ->add('comentario', null, array('label' => ('comentario')))

        ;
    }

    public function getBatchActions() {
        $actions = parent::getBatchActions();
        unset($actions['delete']);
        return $actions;
    }

}
