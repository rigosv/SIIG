<?php

namespace MINSAL\GridFormBundle\Twig\Extension;

use Symfony\Component\DependencyInjection\ContainerInterface;  
use \Twig_Extension;

class VarsExtension extends Twig_Extension
{
    protected $container;

    public function __construct(ContainerInterface $container) 
    {
        $this->container = $container;
    }

    public function getName() 
    {
        return 'some.extension';
    }

    public function getFilters() {
        return array(
            'json_decode'   => new \Twig_SimpleFilter('jsonDecode', array($this, 'jsonDecode')),
        );
    }

    public function jsonDecode($str, $assoc = false) {
        return json_decode($str, $assoc);
    }
}