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
            ->add('codigo', null, array('label'=> ('_codigo_')))
            ->add('nombre', null, array('label'=> ('_nombre_')))
            ->add('descripcion', null, array('label'=> ('_descripcion_')))
            ->add('version', null, array('label'=> ('_version_')))
            ->add('columnasFijas', null, array('label'=> ('_columnas_fijas_')))
            ->add('posicion', null, array('label'=> ('_posicion_listados_')))
            ->add('origenDatos', null, array('label'=> ('_origen_formulario_')))
            ->add('areaCosteo', 'choice', array('label' => ('_area_costeo_'),
                        'choices' => array('rrhh'=>('_rrhh_'),
                            'ga_af'=>('_ga_af_'),
                            'ga_compromisosFinancieros' => ('_ga_compromisos_financieros_'),
                            'ga_variables' => ('_ga_variables_'),
                            'ga_distribucion' => ('_ga_distribucion_'),
                            'ga_costos' => ('_ga_costos_'),
                            'almacen_datos' => ('_almacen_datos_'),
                            'calidad' => ('_calidad_')
                            )
                        ))            
            ->add('meta', null, array('label'=> ('_umbral_estandar_')))
            ->add('formaEvaluacion', 'choice', array('label' => ('_forma_evaluacion_'),
                        'choices' => array(
                            'lista_chequeo'=>('_lista_chequeo_'),
                            'rango_colores' => ('_rango_colores_')
                            )
                        ))
            ->add('periodoLecturaDatos', 'choice', array('label' => ('_periodo_lectura_datos_'),
                        'choices' => array('mensual'=>('_mensual_'),
                            'anual'=>('_anual_')                            
                            )
                        ))
            ->add('campos', null, 
                    array('label'=> ('_campos_'), 
                        'expanded' => false, 
                        'multiple' => true,
                        'by_reference' => false,
                        'class' => 'GridFormBundle:Campo',
                            'query_builder' => function ($repository) {
                                return $repository->createQueryBuilder('c')
                                        ->join('c.significadoCampo', 's')
                                        ->orderBy('s.descripcion');
                            }))
            ->add('rutaManualUso', null, array('label'=> ('_ruta_manual_uso_')))
            ->add('evaluacionPorExpedientes', null, array('label'=> ('_evaluacion_por_expedientes_')))
            ->add('ajustarAltoFila', null, array('label'=> ('_ajustar_alto_fila_')))
            ->add('ocultarNumeroFila', null, array('label'=> ('_ocultar_numero_fila_')))
            ->add('noOrdenarPorFila', null, array('label'=> ('_no_ordenar_por_fila_')))
            ->add('calculoFilas', null, array('label'=> ('_calculo_filas_formula_')))
            ->add('tituloColumnas', null, array('label'=> ('_titulo_columnas_')))
            ->add('grupoFormularios', null, array(
                'label' => ('_grupo_formularios_'), 
                'required' => false, 
                'expanded' => false,
                'by_reference' => true
                ))
            ->add('formularioSup', null, array('label'=> ('_formulario_superior_')))
            ->add('sqlLecturaDatos', null, array('label'=> ('_sql_lectura_datos_')))
            ->add('origenNumerosExpedientes', null, array('label'=> ('_origen_numeros_expedientes_')))
            ->add('conexionOrigenExpedientes', null, array('label'=> ('_conexion_numeros_expedientes_')))
        ;
                            
        $formMapper
            ->setHelps(array(
                'sqlLecturaDatos' => ('_sql_lectura_datos_help'),
                'tituloColumnas' => ('_titulo_columna_help_'),
                'calculoFilas' => ('_calculo_filas_help_'),
                'origenNumerosExpedientes' => ('_origen_numeros_expedientes_help_'),
                'conexionOrigenExpedientes' => ('_conexion_numeros_expedientes_help_')
            ));
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('codigo', null, array('label'=> ('_codigo_')))
            ->add('nombre', null, array('label'=> ('_nombre_')))            
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id', null, array('label'=> ('_id_')))
            ->addIdentifier('codigo', null, array('label'=> ('_codigo_')))
            ->add('nombre', null, array('label'=> ('_nombre_')))
            ->add('descripcion', null, array('label'=> ('_descripcion_'))) 
        ;
    }

    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        unset($actions['delete']);
        return $actions;
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
