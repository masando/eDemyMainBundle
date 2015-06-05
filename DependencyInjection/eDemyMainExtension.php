<?php

namespace eDemy\MainBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class eDemyMainExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        //$configuration = new Configuration();
        //$config = $this->processConfiguration($configuration, $configs);

        //if($config['enabled']) {
            $this->addClassesToCompile(array(
                'eDemy\\MainBundle\\Controller\\BaseController',
                'eDemy\\MainBundle\\Controller\\ContentController',
                'eDemy\\MainBundle\\Controller\\CssController',
                'eDemy\\MainBundle\\Controller\\FooterController',
                'eDemy\\MainBundle\\Controller\\HeaderController',
                'eDemy\\MainBundle\\Controller\\JsController',
                'eDemy\\MainBundle\\Controller\\MainController',
                'eDemy\\MainBundle\\Controller\\MenuController',
                'eDemy\\MainBundle\\Controller\\MetaController',
                'eDemy\\MainBundle\\Controller\\ParamController',
                'eDemy\\MainBundle\\Controller\\ServiceContainerController',
                'eDemy\\MainBundle\\Controller\\RedirectController',
                'eDemy\\MainBundle\\Menu\\Builder',
                'eDemy\\MainBundle\\Event\\ContentEvent',
            ));
            $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
            $loader->load('services.yml');
        //}
    }
}
