<?php

namespace eDemy\MainBundle\Twig;

use Symfony\Component\DependencyInjection\Container;

class ServiceExtension extends \Twig_Extension
{
    /** @var Container $container */
    private $container;
    
    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('service', array($this, 'serviceFunction')),
        );
    }

    public function serviceFunction($service)
    {
        if ($this->container->has($service)) {
            return true;
        } else {
            return false;
        }
    }

    public function getName()
    {
        return 'edemy_service_extension';
    }
}
