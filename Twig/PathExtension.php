<?php

namespace eDemy\MainBundle\Twig;

//use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PathExtension extends \Twig_Extension
{
    private $container;
    
    public function __construct(ContainerInterface $container)
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
        //if($options) die(var_dump($route));
        $router = $this->container->get('router');
        $ruta = $this->container->get('edemy.main')->getNamespace() . '.' . $route;
        if($router->getRouteCollection()->get($ruta) != null) {
            return $router->generate($ruta, $options);
        } elseif($router->getRouteCollection()->get($route) != null) {
            return $router->generate($route, $options);
        } else {
            return false;
        }
    }

    public function getName()
    {
        return 'edemy_path_extension';
    }
}
