<?php
/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 */

namespace MINSAL\IndicadoresBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Provider\MenuProviderInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\AdminBundle\Event\ConfigureMenuEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Sonata menu builder.
 *
 * @author Martin Hasoň <martin.hason@gmail.com>
 */
class MenuBuilder
{
    private $pool;
    private $factory;
    private $provider;
    private $request;
    private $eventDispatcher;
    
    protected $context;

    /**
     * Constructor.
     *
     * @param Pool                     $pool
     * @param FactoryInterface         $factory
     * @param MenuProviderInterface    $provider
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Pool $pool, FactoryInterface $factory, MenuProviderInterface $provider, EventDispatcherInterface $eventDispatcher, $context)
    {
        $this->pool = $pool;
        $this->factory = $factory;
        $this->provider = $provider;
        $this->eventDispatcher = $eventDispatcher;
        $this->context = $context;
    }

    /**
     * Builds sidebar menu.
     *
     * @return ItemInterface
     */
    public function createSidebarMenu()
    {
        $menu = $this->factory->createItem('root', array(
            'extras' => array(
                'request' => $this->request,
            ),
        ));
        
        $usuario = $this->context->getToken()->getUser();

        foreach ($this->pool->getAdminGroups() as $name => $group) {            
            
            $nName = explode('.', $name);
            
            $menu
                    ->addChild($nName[0], array('label' => $nName[0]))
                    ->setAttributes(
                            array(
                                'icon' => $group['icon'],
                                'label_catalogue' => $group['label_catalogue'],
                            )
                    )
                    ->setExtra('roles', $group['roles'])
                    ->setExtra('translationdomain', 'messages')
            ;
            if (count($nName) > 1){
                $menu[$nName[0]]
                        ->addChild($nName[1], array('uri' => '#'))
                        ->setExtra('translationdomain', 'messages');
            }
        }
        $menu->addChild('_reportes_');
        
        
        
        $router = $this->pool->getContainer()->get('router');
        if ($usuario != 'anon.') {
            if ($usuario->hasRole('ROLE_SUPER_ADMIN') or $usuario->hasRole('ROLE_USER_TABLERO_CALIDAD')){
                $menu['_calidad_']->addChild('_plan_mejora_', array('uri' => $router->generate('calidad_planmejora')));
            }
        }
        foreach ($this->pool->getAdminGroups() as $nName => $group) {
            $name_ = explode('.', $nName);            
            $name = $name_[0];
            
            foreach ($group['items'] as $item) {
                if (array_key_exists('admin', $item) && $item['admin'] != null) {
                    $admin = $this->pool->getInstance($item['admin']);

                    if ($usuario != 'anon.') {
                        foreach ($admin->getRoutes()->getElements() as $r) {
                            $path = explode('/', $r->getPath());
                            $ruta = array_pop($path);
                            if (in_array($ruta, array('almacenDatos'))) {
                                if ($admin->hasRoute('almacenDatos') and ( $usuario->hasRole('ROLE_SUPER_ADMIN') or $usuario->hasRole('ROLE_USER_CAPTURA_DATOS'))) {
                                    $menu['origen_datos']
                                            ->addChild('_almacen_datos_', array('uri' => $admin->generateUrl('almacenDatos')))
                                            ->setExtra('admin', $admin)
                                    ;
                                }
                            }
                            if (in_array($ruta, array('getFromKobo'))) {
                                if ($admin->hasRoute('getFromKobo') and ( $usuario->hasRole('ROLE_SUPER_ADMIN'))) {
                                    $menu['_calidad_']
                                            ->addChild('_cargar_from_kobo_', array('uri' => $admin->generateUrl('getFromKobo')))
                                            ->setExtra('admin', $admin)
                                    ;
                                }
                            }
                            if (in_array($ruta, array('tablero'))) {
                                if ($admin->hasRoute('tablero') and ( $usuario->hasRole('ROLE_SUPER_ADMIN') or $usuario->hasRole('ROLE_USER_TABLERO'))) {
                                    $menu[$name]
                                            ->addChild('indicador_tablero', array('uri' => $admin->generateUrl('tablero')))
                                            ->setExtra('admin', $admin)
                                    ;
                                }
                            }
                            if (in_array($ruta, array('matrizSeguimiento'))) {
                                if ($admin->hasRoute('matrizSeguimiento') and ( $usuario->hasRole('ROLE_SUPER_ADMIN') or $usuario->hasRole('ROLE_USER_MATRIZ_SEGUIMIENTO'))) {
                                    $menu['_reportes_']
                                            ->addChild('_matriz_seguimiento_', array('uri' => $admin->generateUrl('matrizSeguimiento')))
                                            ->setExtra('admin', $admin)
                                    ;
                                }
                            }
                            if (in_array($ruta, array('pivotTable'))) {
                                if ($admin->hasRoute('pivotTable') and ( $usuario->hasRole('ROLE_SUPER_ADMIN') or $usuario->hasRole('ROLE_USER_PIVOT_TABLE'))) {
                                    $menu[$name]
                                            ->addChild('_tabla_pivote_', array('uri' => $admin->generateUrl('pivotTable')))
                                            ->setExtra('admin', $admin)
                                    ;
                                }
                            }
                            if (in_array($ruta, array('tableroCalidad'))) {
                                if ($admin->hasRoute('tableroCalidad') and ( $usuario->hasRole('ROLE_SUPER_ADMIN') or $usuario->hasRole('ROLE_USER_TABLERO_CALIDAD'))) {
                                    $menu[$name]
                                            ->addChild('_tablero_calidad_', array('uri' => $admin->generateUrl('tableroCalidad')))
                                            ->setExtra('admin', $admin)
                                    ;
                                }
                            }
                            if (in_array($ruta, array('tableroGeneralCalidad'))) {
                                if ($admin->hasRoute('tableroGeneralCalidad') and ( $usuario->hasRole('ROLE_SUPER_ADMIN') or $usuario->hasRole('ROLE_USER_TABLERO_CALIDAD'))) {
                                    $menu[$name]
                                            ->addChild('_tablero_general_calidad_', array('uri' => $admin->generateUrl('tableroGeneralCalidad')))
                                            ->setExtra('admin', $admin)
                                    ;
                                }
                            }
                            if (in_array($ruta, array('rrhhValorPagado', 'rrhhDistribucionHora', 'rrhhCostos', 'gaVariables', 'gaAf', 
                                                'gaCompromisosFinancieros', 'gaDistribucion', 'gaCostos'))
                                ) {
                                if ( $usuario->hasRole('ROLE_SUPER_ADMIN') or $usuario->hasRole('ROLE_USER_COSTEO')) {
                                    $menu['_costeo_']
                                            ->addChild('_'.$ruta.'_', array('uri' => $admin->generateUrl($ruta)))
                                            ->setExtra('admin', $admin)
                                    ;
                                }
                            }
                        }                        
                    }

                    // skip menu item if no `list` url is available or user doesn't have the LIST access rights
                    if (!$admin->hasRoute('list') || !$admin->isGranted('LIST')) {
                        continue;
                    }

                    $label = $admin->getLabel();
                    $route = $admin->generateUrl('list');
                    $translationDomain = $admin->getTranslationDomain();
                } else {
                    $label = $item['label'];
                    $route = $this->router->generate($item['route'], $item['route_params']);
                    $translationDomain = $group['label_catalogue'];
                    $admin = null;
                }

                if (count($name_) > 1){
                    $menu[$name]->getChild($name_[1])
                            ->addChild($label, array('uri' => $route))
                            ->setExtra('translationdomain', $translationDomain)
                            ->setExtra('admin', $admin)
                    ;
                } else {
                    $menu[$name]
                            ->addChild($label, array('uri' => $route))
                            ->setExtra('translationdomain', $translationDomain)
                            ->setExtra('admin', $admin)
                    ;
                }
            }
            if (0 === count($menu[$name]->getChildren())) {
                $menu->removeChild($name);
            }
        }
        if (0 === count($menu['_reportes_']->getChildren())) {
            $menu->removeChild('_reportes_');
        }
        $event = new ConfigureMenuEvent($this->factory, $menu);
        $this->eventDispatcher->dispatch(ConfigureMenuEvent::SIDEBAR, $event);
        //var_dump($event->getMenu()->getChild('_calidad2_')->getChild('_catalogos_'));
        return $event->getMenu();
    }

    /**
     * Sets the request the service
     *
     * @param Request $request
     */
    public function setRequest(Request $request = null)
    {
        $this->request = $request;
    }
}
