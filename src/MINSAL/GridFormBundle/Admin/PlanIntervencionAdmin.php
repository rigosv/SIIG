<?php

namespace MINSAL\GridFormBundle\Admin;


use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\AdminBundle\Admin\AdminInterface;

class PlanIntervencionAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'fechaEvaluacion' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('codigo', null, array('label'=> $this->getTranslator()->trans('_codigo_plan_')))
            ->add('estandar', null, array('label' => $this->getTranslator()->trans('_estandar_'),
                'required' => true, 'expanded' => false,
                'class' => 'GridFormBundle:Formulario',
                'query_builder' => function ($repository) {                        
                    return $repository->createQueryBuilder('f')
                            ->where('f.areaCosteo = :area ')
                            ->andwhere('f.formularioSup is NULL')
                            ->add('orderBy','f.posicion')
                            ->setParameter('area','calidad')
                        ;
                    
                }))
            ->add('fechaEvaluacion', 'sonata_type_date_picker', array('label'=> $this->getTranslator()->trans('_fecha_evaluacion_')))
            ->add('establecimiento', null, array('label' => $this->getTranslator()->trans('_establecimiento_'),
                    'required' => true, 'expanded' => false,
                    'class' => 'CostosBundle:Estructura',
                    'query_builder' => function ($repository) {                        
                        return $repository->createQueryBuilder('e')
                                ->where('e.nivel = 1 ')
                                ->add('orderBy','e.nombre');
                    }))
            ->add('situacionEncontrada', null, array('label'=> $this->getTranslator()->trans('_situacion_encontrada_')))            
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('estandar', null, array('label'=> $this->getTranslator()->trans('_estandar_')))            
            ->add('fechaEvaluacion', null, array('label'=> $this->getTranslator()->trans('_fecha_evaluacion_')))
            ->add('establecimiento', null, array('label'=> $this->getTranslator()->trans('_establecimiento_')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('label'=> $this->getTranslator()->trans('_codigo_')))
            ->add('estandar', null, array('label'=> $this->getTranslator()->trans('_estandar_')))
            ->add('fechaEvaluacion', null, array('label'=> $this->getTranslator()->trans('_fecha_evaluacion_')))
            ->add('establecimiento', null, array('label'=> $this->getTranslator()->trans('_establecimiento_')))
            ->add('situacionEncontrada', null, array('label'=> $this->getTranslator()->trans('_situacion_encontrada_')))
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!$childAdmin && !in_array($action, array('edit'))) {
            return;
        }
        $admin = $this->isChild() ? $this->getParent() : $this;
        $id = $admin->getRequest()->get('id');
        $menu->addChild(
            $this->trans('_plan_intervencion_'),
            array('uri' => $admin->generateUrl('edit', array('id' => $id)))
        );
        $menu->addChild(
            $this->trans('_actividades_'),
            array('uri' => $admin->generateUrl('sonata.gridform.admin.plan_actividad.list', array('id' => $id)))
        );        
    }
}
