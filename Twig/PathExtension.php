<?php

namespace eDemy\MainBundle\Twig;

//use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class PathExtension extends \Twig_Extension
{
    /** @var ContainerInterface $this->container */
    protected $container;
    
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('asset_url', array($this, 'assetUrl'), array('is_safe' => array('html'), 'pre_escape' => 'html')),
            new \Twig_SimpleFilter('script_url', array($this, 'scriptUrl'), array('is_safe' => array('html'), 'pre_escape' => 'html')),
        );
    }

    public function scriptUrl($file) {
        $scriptLink = $this->pathFunction('edemy_js_file', array(
            'file' => $file
        ));
        if($scriptLink) {
            $script = '<script src="' . $scriptLink . '" type="text/javascript"></script>';

            return $script;
        }

        return false;
    }

    public function assetUrl($file) {
        $assetLink = $this->pathFunction('edemy_css_file', array(
            'file' => $file
        ));
        if($assetLink) {
            $asset = '<link rel="stylesheet" href="' . $assetLink . '" />';

            return $asset;
        }

        return false;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('path', array($this, 'pathFunction')),
        );
    }

    public function pathFunction($_route, $options = array())
    {
        //if($options) die(var_dump($route));
        $router = $this->container->get('router');
        $ruta = $this->container->get('edemy.main')->getNamespace() . '.' . $_route;
        if($router->getRouteCollection()->get($ruta) != null) {
            return $router->generate($ruta, $options);
        } elseif($router->getRouteCollection()->get($_route) != null) {
            return $router->generate($_route, $options);
        } else {
            return false;
        }
    }

    public function getName()
    {
        return 'edemy_path_extension';
    }
}
