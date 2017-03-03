<?php

namespace MINSAL\IndicadoresBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin as Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\CoreBundle\Validator\ErrorElement;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\AdminBundle\Form\FormMapper;

class CampoAdmin extends Admin
{
    protected $datagridValues = array(
        '_page' => 1, // Display the first page (default = 1)
        '_sort_order' => 'ASC', // Descendant ordering (default = 'ASC')
        '_sort_by' => 'origenDato.nombre' // name of the ordered field (default = the model id field, if any)
    );

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
                ->add('nombre', null, array('label' => ('nombre')))
                ->add('descripcion', null, array('label' => ('descripcion'), 'required' => true))
                ->add('origenDato', null, array('label' => ('origen_datos'), 'required' => true))
                ->add('tipoCampo', null, array('label' => ('javascript.tipo'), 'required' => true))
                ->add('significado', null, array('label' => ('significado'), 'required' => true))
                ->add('formula', null, array('label' => ('formula'), 'required' => true))
                ->end()
        ;
    }

    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('nombre', null, array('label' => ('nombre')))
                ->add('origenDato', null, array('label' => ('origen_datos')))
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->add('nombre', null, array('label' => ('nombre')))
                ->add('descripcion', null, array('label' => ('descripcion')))
                ->add('origenDato.nombre', null, array('label' => ('origen_datos')))
                ->add('tipoCampo.descripcion', null, array('label' => ('tipo')))
                ->add('significado.descripcion', null, array('label' => ('significado')))
                ->add('_action', 'actions', array(
                    'actions' => array(
                        'edit' => array()
                    )
                ))
        ;
    }

    /**
     * @return \Sonata\AdminBundle\Datagrid\ProxyQueryInterface
     */
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);

        return new ProxyQuery(
                $query
                        ->where($query->expr()->isNotNull($query->getRootAlias() . '.formula'))
        );
    }

    public function getTemplate($name)
    {
        switch ($name) {
            case 'edit':
                return 'IndicadoresBundle:CRUD:campo-edit.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

    public function validate(ErrorElement $errorElement, $object)
    {
        $vars_formula = array();
        $formula = str_replace(' ', '', $object->getFormula());
        preg_match_all('/(\{[\w]+\})/', $formula, $vars_formula);

        //Verificar que haya utilizado solo campos existentes en el origen de datos
        foreach ($object->getOrigenDato()->getCampos() as $campo) {
            if ($campo->getSignificado() and $campo->getTipoCampo()){
                $campos[$campo->getSignificado()->getCodigo()] = $campo->getTipoCampo()->getCodigo();
            }
        }

        //Verificar que todas las variables sean campos del origen de datos
        foreach ($vars_formula[0] as $var) {
            if (!array_key_exists(str_replace(array('{', '}'), '', $var), $campos)) {
                $errorElement
                        ->with('formula')
                        ->addViolation('<span style="color:red">' . $var . '</span> ' . ('_variable_no_campo_'))
                        ->end();

                return;
            }
        }
        // ******** Verificar si matematicamente la fórmula es correcta
        // 1) Sustituir las variables por valores aleatorios entre 1 y 100
        foreach ($vars_formula[0] as $var) {
            $variable = str_replace(array('{', '}'), '', $var);

            if ($campos[$variable] == 'integer' or $campos[$variable] == 'float') {
                $formula = str_replace($var, rand(1, 100), $formula);
            } elseif ($campos[$variable] == 'date') {
                $formula = str_replace($var, ' current_date ', $formula);
            } else {
                $formula = str_replace($var, "'texto'", $formula);
            }
        }

        //evaluar la formula
        try {
            $sql = 'SELECT ' . $formula;

            $this->getConfigurationPool()
                    ->getContainer()
                    ->get('doctrine')
                    ->getEntityManager()
                    ->getConnection()
                    ->executeQuery($sql);
        } catch (\Doctrine\DBAL\DBALException $exc) {
            $errorElement
                    ->with('formula')
                    ->addViolation(('sintaxis_invalida') . $exc->getMessage())
                    ->end();
        }
    }

}
