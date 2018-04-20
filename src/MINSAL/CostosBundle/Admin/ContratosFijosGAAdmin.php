<?php

namespace MINSAL\CostosBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;

class ContratosFijosGAAdmin extends Admin
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
            ->add('descripcion', null, array('label'=> ('_descripcion_')))
            ->add('categoria', null, array('label'=> ('_categoria_')))
            ->add('criterioDistribucion', null, array('label'=> ('_criterio_distribucion_')))
            ->add('establecimientos', null, array('label' => ('_establecimiento_'),
                    'required' => true, 'expanded' => true,
                    'class' => 'CostosBundle:Estructura',
                    'by_reference' => false,
                    'property' => 'codigoNombre',
                    'query_builder' => function ($repository) {                        
                        return $repository->createQueryBuilder('e')
                                ->where('e.nivel = 1 ')
                                ->add('orderBy','e.nombre');
                    }))
            ->add('ubicacion', null, array('label' => ('_ubicacion_'),
                    'required' => false, 'expanded' => false,
                    'class' => 'CostosBundle:Ubicacion',
                    'property' => 'codigoEstablecimientoNombreUbicacion',
                    'query_builder' => function ($repository) {                        
                        return $repository->createQueryBuilder('u')
                                ->join('u.establecimiento', 'e')
                                ->add('orderBy','e.nombre');
                    }))
            ->add('variableCalculoConsumo', null, array('label' => ('_variable_calculo_consumo_'),
                    'required' => false, 'expanded' => false,
                    'class' => 'GridFormBundle:Campo',
                    'query_builder' => function ($repository) {                        
                        return $repository->createQueryBuilder('c')
                                ->join('c.formularios', 'f')
                                ->join('c.significadoCampo', 's')
                                ->where("f.areaCosteo = 'ga_variables' ")
                                ->add('orderBy','s.descripcion');
                    }))
            ->setHelps(array(
                'variableCalculoConsumo' => ('_ayuda_variable_calculo_consumo_')                
            ))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('descripcion', null, array('label'=> ('_descripcion_')))
            ->add('categoria', null, array('label'=> ('_categoria_')))
            ->add('criterioDistribucion', null, array('label'=> ('_criterio_distribucion_')))
            ->add('establecimientos', null, array('label'=> ('_establecimiento_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('codigo', null, array('label'=> ('_codigo_')))
            ->add('descripcion', null, array('label'=> ('_descripcion_'))) 
            ->add('categoria', null, array('label'=> ('_categoria_')))
            ->add('criterioDistribucion', null, array('label'=> ('_criterio_distribucion_')))
            ->add('establecimientos', null, array('label' => ('_establecimiento_')))
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);
        return $actions;
    }
    
    public function validate(ErrorElement $errorElement, $object)
    {        
        if ($object->getUbicacion() != null){
            // Si elije una ubicación en particular para este compromiso
            // solo debe seleccionar el establecimiento al que pertenece 
            // la ubicación
            foreach ($object->getEstablecimientos() as $e){
                if ($e->getCodigo() != $object->getUbicacion()->getEstablecimiento()->getCodigo()){
                    $errorElement
                        ->with('ubicacion')
                            ->addViolation(('_solo_establecimiento_de_ubicacion_'))
                        ->end()
                        ->with('establecimientos')
                            ->addViolation(('_solo_establecimiento_de_ubicacion_'))
                        ->end();
                    break;
                }
            }
        }     
    }
}
