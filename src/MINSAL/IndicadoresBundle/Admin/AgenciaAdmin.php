<?php

namespace MINSAL\IndicadoresBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class AgenciaAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'codigo' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->with($this->getTranslator()->trans('_general_'), array('class' => 'col-md-6'))
                ->add('codigo', null, array('label'=> $this->getTranslator()->trans('codigo')))
                ->add('nombre', null, array('label'=> $this->getTranslator()->trans('nombre')))
            ->end()
            ->with($this->getTranslator()->trans('_accesos_'), array('class' => 'col-md-6'))
                ->add('indicadores', null, 
                        array(
                            'label' => $this->getTranslator()->trans('indicadores'), 
                            'expanded' => true
                            )
                    )
            ->end()
        ;
        
        
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('codigo', null, array('label'=> $this->getTranslator()->trans('codigo')))
            ->add('nombre',null, array('label'=> $this->getTranslator()->trans('nombre')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('codigo', null, array('label'=> $this->getTranslator()->trans('codigo')))
            ->add('nombre', null, array('label'=> $this->getTranslator()->trans('nombre')))            
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }
}
