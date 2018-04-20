<?php

namespace MINSAL\IndicadoresBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
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
            ->tab(('_general_'))
                ->with('', array('class' => 'col-md-6'))
                    ->add('codigo', null, array('label'=> ('codigo')))
                    ->add('nombre', null, array('label'=> ('nombre')))
                ->end()
            ->end()
            ->tab(('_accesos_'))
                ->with((' '), array('class' => 'col-md-6'))
                    ->add('indicadores', null, 
                            array(
                                'label' => ('indicadores'), 
                                'expanded' => false,
                                'class' => 'IndicadoresBundle:FichaTecnica',
                                'query_builder' => function ($repository) {
                                    return $repository->createQueryBuilder('ft')
                                            ->orderBy('ft.nombre');
                                    }
                                )
                        )                    
                ->end()
                ->with((''), array('class' => 'col-md-6'))
                    ->add('formularios', null, 
                            array(
                                'label' => ('_formularios_'), 
                                'expanded' => false,
                                'class' => 'GridFormBundle:Formulario',
                                'query_builder' => function ($repository) {
                                    return $repository->createQueryBuilder('f')
                                            ->orderBy('f.posicion, f.nombre');
                                    }
                                )
                        )
                ->end()
            ->end()
        ;
        
        
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('codigo', null, array('label'=> ('codigo')))
            ->add('nombre',null, array('label'=> ('nombre')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('codigo', null, array('label'=> ('codigo')))
            ->add('nombre', null, array('label'=> ('nombre')))            
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);
        return $actions;
    }
}
