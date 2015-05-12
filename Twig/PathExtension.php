<?php

namespace eDemy\MainBundle\Twig;

use Symfony\Component\EventDispatcher\GenericEvent;

class PathExtension extends \Twig_Extension
{
    private $container;
    
    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('path', array($this, 'pathFunction')),
        );
    }

    public function pathFunction($route, $options = array())
    {
        //if($options) die(var_dump($options));
        $router = $this->container->get('router');
        $ruta = $this->container->get('edemy.main')->getNamespace() . '.' . $route;
        if($router->getRouteCollection()->get($ruta) != null) {
            return $router->generate($ruta, $options);
        } else {
            return $router->generate($route, $options);
        }
    }

    public function getName()
    {
        return 'edemy_path_extension';
    }
}
