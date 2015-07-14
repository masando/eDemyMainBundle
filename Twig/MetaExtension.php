<?php

namespace eDemy\MainBundle\Twig;

//use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\DependencyInjection\ContainerInterface;

class MetaExtension extends \Twig_Extension
{
    /** @var ContainerInterface $this->container */
    protected $container;
    
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('metas', array($this, 'metasFunction'), array('is_safe' => array('html'), 'pre_escape' => 'html')),
        );
    }

    public function metasFunction()
    {
        $edemyMeta = $this->container->get('edemy.meta');
        $metas = $edemyMeta->getMetas();
        //die(var_dump($metas));

        $content = "";
        foreach($metas as $meta) {
            foreach ($meta as $id => $value) {
                $content .= '<meta property="'.$id.'" content="'.$value.'" />';
            }
        }

        return $content;
    }

    public function getName()
    {
        return 'edemy_meta_extension';
    }
}
