<?php

namespace MINSAL\CalidadBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class GestionPermisosAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('estandares', null, array('label'=> ('_estandar_'), 'required' => true, 'multiple'=>true))
            ->add('establecimiento', null, array('label'=> ('_establecimiento_'), 'required' => true,))
            ->add('establecimiento', null, array('label' => ('_establecimiento_'),
                'required' => true,
                'class' => 'CostosBundle:Estructura',
                'query_builder' => function ($repository) {
                    return $repository->createQueryBuilder('e')
                            ->where('e.nivel = 1');
                }))
            ->add('usuario', null, array('label'=> ('_usuario_'), 'required' => true,))
            ->add('elemento', ChoiceType::class, array('label' => ('_elemento_'),
                        'choices' => array(
                            'plan_mejora'=>('_plan_mejora_')
                            )
                        ))
            ->add('accion', ChoiceType::class, array('label' => ('_accion_'),
                        'choices' => array(
                            'crear_editar'=>('_crear_editar_'),
                            'ver'=>('_ver_')
                            )
                        ))
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('usuario', null, array('label'=> ('_usuario_')))
            ->add('estandares', null, array('label'=> ('_estandar_')))
            ->add('establecimiento', null, array('label'=> ('_establecimiento_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('label'=> ('_id_')))
            ->add('usuario', null, array('label'=> ('_usuario_')))
            ->add('estandares', null, array('label'=> ('_estandar_')))
            ->add('establecimiento', null, array('label'=> ('_establecimiento_')))
            ->add('elemento', null, array('label'=> ('_elemento_')))
            ->add('accion', null, array('label'=> ('_accion_')))
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }
}
