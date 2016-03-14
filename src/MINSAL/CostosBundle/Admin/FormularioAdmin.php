<?php

namespace MINSAL\CostosBundle\Admin;

use MINSAL\GridFormBundle\Admin\FormularioAdmin AS FormularioAdminBase;
use Sonata\AdminBundle\Route\RouteCollection;

class FormularioAdmin extends FormularioAdminBase
{
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('rrhhValorPagado');
        $collection->add('rrhhDistribucionHora');
        $collection->add('rrhhCostos');
        $collection->add('gaAf');
        $collection->add('gaCompromisosFinancieros');
        $collection->add('gaVariables');
        $collection->add('gaDistribucion');
        $collection->add('gaCostos');        
        parent::configureRoutes($collection);
    }
}
