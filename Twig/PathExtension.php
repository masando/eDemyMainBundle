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
            new \Twig_SimpleFunction('cdnjs', array($this, 'jqueryFunction'), array('is_safe' => array('html'), 'pre_escape' => 'html')),
        );
    }

    public function pathFunction($_route, $options = array())
    {
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

    // @TODO activar y variar con parámetros en la base de datos
    public function jqueryFunction($lib, $version = null, $_route = null)
    {
        switch($lib) {
            case 'jquery':
                $asset = '<script src="////cdnjs.cloudflare.com/ajax/libs/jquery/' . $version . '/jquery.min.js"></script>';
                break;
            case 'hinclude':
                $asset = '<script src="//cdnjs.cloudflare.com/ajax/libs/hinclude/' . $version . '/hinclude.min.js" type="text/javascript"></script>';
                break;
            case 'jquery.slicknav':
                $asset = '<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/SlickNav/' . $version . '/slicknav.css" />';
                $asset .= '<script src="//cdnjs.cloudflare.com/ajax/libs/SlickNav/' . $version . '/jquery.slicknav.min.js"></script>';
                break;
            case 'superfish':
                $asset = '<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/superfish/' . $version . '/superfish.min.css"/>';
                $asset .= '<script src="//cdnjs.cloudflare.com/ajax/libs/superfish/' . $version . '/superfish.min.js"></script>';
                break;
            case 'backstretch':
                $asset = '<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-backstretch/' . $version . '/jquery.backstretch.min.js" type="text/javascript"></script>';
                break;
            case 'instafeed':
                $asset = '<script src="https://raw.github.com/stevenschobert/instafeed.js/master/instafeed.min.js" type="text/javascript"></script>';
                break;

        }

        return $asset;
    }

    public function getName()
    {
        return 'edemy_path_extension';
    }
}
