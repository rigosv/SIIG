<?php

namespace MINSAL\IndicadoresBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\CoreBundle\Validator\ErrorElement;

class SignificadoCampoAdmin extends Admin
{
    private $repository;
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'descripcion' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
                ->tab(('_general_'))
                    ->with('')
                        ->add('codigo', null, array('label' => ('codigo')))
                        ->add('descripcion', null, array('label' => ('descripcion')))
                        ->add('usoCosteo', null, array('label' => ('_uso_costeo_')))
                        ->add('acumulable', null, array('label' => ('_acumulable_')))
                        ->add('usoEnCatalogo', null, array('label' => ('uso_catalogo')))
                        ->add('catalogo', 'choice', array('label' => ('catalogo'),
                            'required' => false,
                            'choices' => $this->repository->getCatalogos()

                        ))
                        ->add('tiposGraficos', null, array('label' => ('_tipos_graficos_'),
                                    'expanded' => true
                                ))
                    ->end()
                ->end()                
                ->tab(('_datos_geograficos_'), array('collapsed' => false))
                    ->with('')
                        ->add('nombreMapa', null, array('label' => ('nombre_archivo_mapa')))
                        ->add('escala', null, array('label' => ('_escala_')))
                        ->add('origenX', null, array('label' => ('_origen_x_')))
                        ->add('origenY', null, array('label' => ('_origen_y_')))
                    ->end()
                ->end()
                ;
        
        $formMapper
            ->setHelps(array(
                    'acumulable' => ('_acumulable_help_')
                ))
                ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('descripcion', null, array('label' => ('descripcion')))
                ->add('catalogo', null, array('label' => ('catalogo')))
                ->add('usoCosteo', null, array('label' => ('_uso_costeo_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('codigo', null, array('label' => ('codigo')))
                ->add('descripcion', null, array('label' => ('descripcion')))
                ->add('usoEnCatalogo', null, array('label' => ('uso_catalogo')))
                ->add('catalogo', null, array('label' => ('catalogo')))

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
        //Marcó la opción que se usará en catálogo pero no ha elegido un catálog
        if ($object->getUsoEnCatalogo() == true and $object->getCatalogo() != '') {
            $errorElement
                    ->with('catalogo')
                    ->addViolation(('no_catalogo_y_describir_catalogo'))
                    ->end();
        }
    }

    public function setRepository($repository)
    {
        $this->repository = $repository;
    }

    public function prePersist($Significado)
    {
        $util = new \MINSAL\IndicadoresBundle\Util\Util();
        $Significado->setCodigo($util->slug($Significado->getCodigo()));
    }

    public function preUpdate($Significado)
    {
        $util = new \MINSAL\IndicadoresBundle\Util\Util();
        $Significado->setCodigo($util->slug($Significado->getCodigo()));
    }

}
