<?php

namespace eDemy\MainBundle\Twig;

use Symfony\Component\DependencyInjection\ContainerInterface;
 
class LogoExtension extends \Twig_Extension
{
    protected $container, $notify;
 
    public function __construct(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->logo = $container->get("edemy.logo");
    }

    public function getName()
    {
        return 'edemy_logo_show_extension';
    }
 
    public function renderLogo($container = false)
    {
        //$logo = $this->notify->all();
        $logo = $this->logo->getParam('logo');
        if($logo != "none") {
            return $this->container->get("templating")->render("eDemyLogoBundle::logo_show.html.twig", array(
                'logo' => $logo,
            ));
        }
 
        return null;
    }

    public function getFunctions()
    {
        return array(
            'edemy_logo_show' => new \Twig_Function_Method($this, 'renderLogo', array('is_safe' => array('html'))),
        );
    }
}
