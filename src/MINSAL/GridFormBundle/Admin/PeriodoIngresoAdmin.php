<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;

class PeriodoIngresoAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'anio' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('anio', null, array('label'=> ('_anio_')))
            ->add('mes', 'choice', array('label' => ('_mes_'),
                        'choices' => 
                            array( '00' => ('_solo_lectura_'),
                                '01'=>('_enero_'),
                                '02' => ('_febrero_'),
                                '03' => ('_marzo_'),
                                '04' => ('_abril_'),
                                '05' => ('_mayo_'),
                                '06' => ('_junio_'),
                                '07' => ('_julio_'),
                                '08' => ('_agosto_'),
                                '09' => ('_septiembre_'),
                                '10' => ('_octubre_'),
                                '11' => ('_noviembre_'),
                                '12' => ('_diciembre_'),
                                '01_p'=>('_enero_p_'),
                                '02_p' => ('_febrero_p_'),
                                '03_p' => ('_marzo_p_'),
                                '04_p' => ('_abril_p_'),
                                '05_p' => ('_mayo_p_'),
                                '06_p' => ('_junio_p_'),
                                '07_p' => ('_julio_p_'),
                                '08_p' => ('_agosto_p_'),
                                '09_p' => ('_septiembre_p_'),
                                '10_p' => ('_octubre_p_'),
                                '11_p' => ('_noviembre_p_'),
                                '12_p' => ('_diciembre_p_')
                            )
                        ))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('anio', null, array('label'=> ('_anio_')))
            ->add('mes', null, array('label'=> ('_mes_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper            
            ->addIdentifier('anio', null, array('label'=> ('_anio_')))
            ->addIdentifier('mes', null, array('label'=> ('_mes_')))
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);
        return $actions;
    }
}
