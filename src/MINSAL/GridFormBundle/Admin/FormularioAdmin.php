<?php

namespace MINSAL\GridFormBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class FormularioAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'codigo' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('codigo', null, array('label'=> $this->getTranslator()->trans('_codigo_')))
            ->add('nombre', null, array('label'=> $this->getTranslator()->trans('_nombre_')))
            ->add('descripcion', null, array('label'=> $this->getTranslator()->trans('_descripcion_')))
            ->add('version', null, array('label'=> $this->getTranslator()->trans('_version_')))
            ->add('columnasFijas', null, array('label'=> $this->getTranslator()->trans('_columnas_fijas_')))
            ->add('posicion', null, array('label'=> $this->getTranslator()->trans('_posicion_listados_')))
            ->add('origenDatos', null, array('label'=> $this->getTranslator()->trans('_origen_formulario_')))
            ->add('areaCosteo', 'choice', array('label' => $this->getTranslator()->trans('_area_costeo_'),
                        'choices' => array('rrhh'=>$this->getTranslator()->trans('_rrhh_'),
                            'ga_af'=>$this->getTranslator()->trans('_ga_af_'),
                            'ga_compromisosFinancieros' => $this->getTranslator()->trans('_ga_compromisos_financieros_'),
                            'ga_variables' => $this->getTranslator()->trans('_ga_variables_'),
                            'ga_distribucion' => $this->getTranslator()->trans('_ga_distribucion_'),
                            'ga_costos' => $this->getTranslator()->trans('_ga_costos_'),
                            'almacen_datos' => $this->getTranslator()->trans('_almacen_datos_'),
                            'calidad' => $this->getTranslator()->trans('_calidad_')
                            )
                        ))            
            ->add('meta', null, array('label'=> $this->getTranslator()->trans('_umbral_estandar_')))
            ->add('formaEvaluacion', 'choice', array('label' => $this->getTranslator()->trans('_forma_evaluacion_'),
                        'choices' => array(
                            'lista_chequeo'=>$this->getTranslator()->trans('_lista_chequeo_'),
                            'rango_colores' => $this->getTranslator()->trans('_rango_colores_')
                            )
                        ))
            ->add('periodoLecturaDatos', 'choice', array('label' => $this->getTranslator()->trans('_periodo_lectura_datos_'),
                        'choices' => array('mensual'=>$this->getTranslator()->trans('_mensual_'),
                            'anual'=>$this->getTranslator()->trans('_anual_')                            
                            )
                        ))
            ->add('campos', null, 
                    array('label'=> $this->getTranslator()->trans('_campos_'), 
                        'expanded' => false, 
                        'multiple' => true,
                        'by_reference' => false,
                        'class' => 'GridFormBundle:Campo',
                            'query_builder' => function ($repository) {
                                return $repository->createQueryBuilder('c')
                                        ->join('c.significadoCampo', 's')
                                        ->orderBy('s.descripcion');
                            }))
            ->add('rutaManualUso', null, array('label'=> $this->getTranslator()->trans('_ruta_manual_uso_')))
            ->add('evaluacionPorExpedientes', null, array('label'=> $this->getTranslator()->trans('_evaluacion_por_expedientes_')))
            ->add('ajustarAltoFila', null, array('label'=> $this->getTranslator()->trans('_ajustar_alto_fila_')))
            ->add('ocultarNumeroFila', null, array('label'=> $this->getTranslator()->trans('_ocultar_numero_fila_')))
            ->add('noOrdenarPorFila', null, array('label'=> $this->getTranslator()->trans('_no_ordenar_por_fila_')))
            ->add('calculoFilas', null, array('label'=> $this->getTranslator()->trans('_calculo_filas_formula_')))
            ->add('tituloColumnas', null, array('label'=> $this->getTranslator()->trans('_titulo_columnas_')))
            ->add('grupoFormularios', null, array(
                'label' => $this->getTranslator()->trans('_grupo_formularios_'), 
                'required' => false, 
                'expanded' => false,
                'by_reference' => true
                ))
            ->add('formularioSup', null, array('label'=> $this->getTranslator()->trans('_formulario_superior_')))
            ->add('sqlLecturaDatos', null, array('label'=> $this->getTranslator()->trans('_sql_lectura_datos_')))
            ->add('origenNumerosExpedientes', null, array('label'=> $this->getTranslator()->trans('_origen_numeros_expedientes_')))
            ->add('conexionOrigenExpedientes', null, array('label'=> $this->getTranslator()->trans('_conexion_numeros_expedientes_')))
        ;
                            
        $formMapper
            ->setHelps(array(
                'sqlLecturaDatos' => $this->getTranslator()->trans('_sql_lectura_datos_help'),
                'tituloColumnas' => $this->getTranslator()->trans('_titulo_columna_help_'),
                'calculoFilas' => $this->getTranslator()->trans('_calculo_filas_help_'),
                'origenNumerosExpedientes' => $this->getTranslator()->trans('_origen_numeros_expedientes_help_'),
                'conexionOrigenExpedientes' => $this->getTranslator()->trans('_conexion_numeros_expedientes_help_')
            ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('codigo', null, array('label'=> $this->getTranslator()->trans('_codigo_')))
            ->add('nombre', null, array('label'=> $this->getTranslator()->trans('_nombre_')))            
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('label'=> $this->getTranslator()->trans('_id_')))
            ->addIdentifier('codigo', null, array('label'=> $this->getTranslator()->trans('_codigo_')))
            ->add('nombre', null, array('label'=> $this->getTranslator()->trans('_nombre_')))
            ->add('descripcion', null, array('label'=> $this->getTranslator()->trans('_descripcion_'))) 
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['delete'] = null;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('almacenDatos'); 
        $collection->add('getFromKobo'); 
    }
    
    public function prePersist($Formulario)
    {
        $this->setGrupoFormularios($Formulario);
    }
    
    public function preUpdate($Formulario)
    {
        $this->setGrupoFormularios($Formulario); 
    }
    
    protected function setGrupoFormularios($Formulario){        
        if ($this->getForm()->getData()->getGrupoFormularios() != null)
            $Formulario->setGrupoFormularios($this->getForm()->getData()->getGrupoFormularios()->getValues());        
    }
}
