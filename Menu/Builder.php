<?php

namespace eDemy\MainBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;
use Symfony\Component\EventDispatcher\GenericEvent;

class Builder extends ContainerAware
{
    public function menu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $translator = $this->container->get('translator');
        $items = $options['items'];
        //$items = $this->container->get('doctrine.orm.entity_manager')
        //    ->getRepository('eDemyMainBundle:Param')->findBy(array(
        //    'type' => 'mainmenu',
        //    ));
        $router = $this->container->get('router');
        //die(var_dump($items));    
        if($items != null) {
            foreach($items as $item){
                //opciones del menú
                $options = array();
                if($item->getDescription() != null) {
                    $options = json_decode($item->getDescription(), true);
                }
                //si hay ruta válida
                if($router->getRouteCollection()->get($item->getValue()) != null) {
                    if($item->getNamespace()) {
                        $route = $item->getNamespace() . '.' . $item->getValue();
                    } else {
                        $route = $item->getValue();
                    }

                    $parts = explode('_', $item->getName());
                    if(count($parts) == 2) {
                        $parent = $parts[0];
                        $child = $parts[1];
                        if($menu[$parent]) {
                            $menu[$parent]->addChild($child, array(
                                'route' => $route,
                                'routeParameters' => $options,
                            ));
                        }
                    } else {
                        $menu->addChild($item->getName(), array(
                            'route' => $route,
                            'routeParameters' => $options,
                        ));
                    }
                } else {
                    if(array_key_exists('url', $options)) {
                        $options = array(
                            'uri' => $options['url'],
                        );
                    }
                    $parts = explode('_', $item->getName());
                    if(count($parts) == 2) {
                        $parent = $parts[0];
                        $child = $parts[1];
                        if($menu[$parent]) {
                            if($menu[$parent]->addChild($item->getValue())) {
                                $menu[$parent]->addChild($item->getValue(), $options)
                                    ->setLinkAttributes(array('target' => '_blank'))
                                ;
                            } else {
                                $menu[$parent]->addChild($item->getName(), $options)
                                    ->setLinkAttributes(array('target' => '_blank'))
                                ;
                            }
                        }
                    } else {
                        $menu->addChild($item->getName(), array_merge($options, array(
                            'label' => $item->getValue(),
                        )))
                            ->setLinkAttributes(array('target' => '_blank'))
                        ;
                    }
                }
            }
        }
        
        return $menu;
    }
    public function adminMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $translator = $this->container->get('translator');
        $menu->addChild($translator->trans('admin.category'), array('route' => 'edemy_main_productcategory_index'));
        $menu->addChild($translator->trans('admin.product.new'), array('route' => 'edemy_main_product_new'));
        $menu->addChild($translator->trans('admin.param'), array('route' => 'edemy_main_param_index'));
        $menu->addChild($translator->trans('admin.todo'), array('route' => 'edemy_main_todo_index'));
        
        return $menu;
    }
    public function transMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $menu->addChild('es', array('route' => 'edemy_main_homepage_index'));
        $request = $this->container->get('request');

        $routename = $request->attributes->get('_route');
        $menu->addChild('es', array(
            'route' => $routename,
            'routeParameters' => array_merge(
                $request->query->get('_route_params'),
                array('_locale' => 'es')
            )
        ));
        $menu->addChild('en', array(
            'route' => $routename,
            'routeParameters' => array_merge(
                $request->query->get('_route_params'),
                array('_locale' => 'en')
            )
        ));

        return $menu;
    }
    public function footerMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');
        $translator = $this->container->get('translator');
        $edemy = $this->container->get('edemy.main');
        /*
        $menu->addChild($translator->trans('faq'), array(
            'route' => 'edemy_main_doc_file',
            'routeParameters' => array( 'file' => 'faq' ) 
            ));
        $menu->addChild($edemy->translate('doc.faq'), array(
            'route' => 'edemy_main_doc_file',
            'routeParameters' => array( 'file' => 'faq' ) 
            ));
        $menu->addChild($edemy->translate('doc.conditions'), array(
            'route' => 'edemy_main_doc_file',
            'routeParameters' => array( 'file' => 'condiciones' ) 
            ));
        $menu->addChild($edemy->translate('doc.privacy'), array(
            'route' => 'edemy_main_doc_file',
            'routeParameters' => array( 'file' => 'privacidad' ) 
            ));
        $menu->addChild($edemy->translate('contact'), array(
            'route' => 'edemy_main_contact_form',
        ));
        */
        
        return $menu;
    }
}
