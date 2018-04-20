<?php

namespace MINSAL\IndicadoresBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Doctrine\ORM\EntityRepository;

class VariableDatoAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'nombre' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
                ->add('origenDatos', 'entity', array('label' => 'origen_dato',
                    'class' => 'IndicadoresBundle:OrigenDatos',
                    'property' => 'nombre',
                    'query_builder' => function(EntityRepository $er) {
                        return $er->createQueryBuilder('od')
                                ->where('od.esCatalogo = :es_catalogo')
                                ->orderBy('od.nombre', 'ASC')
                                ->setParameter('es_catalogo', 'false');
                    }
                ))
                ->add('nombre', null, array('label' => ('nombre_variable')))
                ->add('iniciales', null, array('label' => ('iniciales')))
                ->add('idFuenteDato', null, array('label' => ('fuente_datos')))
                ->add('idResponsableDato', null, array('label' => ('responsable_datos')))
                ->add('confiabilidad', null, array('label' => ('confiabilidad'), 'required'=>false))
                ->add('comentario', null, array('label' => ('comentario'), 'required'=>false))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('nombre', null, array('label' => ('nombre')))
                ->add('iniciales', null, array('label' => ('iniciales')))
                ->add('idResponsableDato', null, array('label' => ('responsable_datos')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('nombre', null, array('label' => 'nombre_variable'))
                ->add('iniciales', null, array('label' => ('iniciales')))
                ->add('origenDatos', null, array('label' => ('origen_dato')))

        ;
    }

    public function validate(ErrorElement $errorElement, $object)
    {
        $campos_no_configurados = $this->getModelManager()->findBy('IndicadoresBundle:Campo',
                array('origenDato' => $object->getOrigenDatos(),
                    'significado' => null));

        if (count($campos_no_configurados) > 0) {
            $errorElement
                ->with('origenDatos')
                    ->addViolation(('origen_no_configurado'))
                ->end();
        }

    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);
        return $actions;
    }

}
