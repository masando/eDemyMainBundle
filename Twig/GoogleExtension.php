<?php

namespace eDemy\MainBundle\Twig;

//use Symfony\Component\EventDispatcher\GenericEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class GoogleExtension extends \Twig_Extension
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
            new \Twig_SimpleFunction('adsense', array($this, 'adsenseFunction'), array('is_safe' => array('html'), 'pre_escape' => 'html')),
        );
    }

    public function adsenseFunction($ad_slot)
    {
        $eDemyGoogle = $this->container->get('edemy.google');
        $ad_client = $eDemyGoogle->getParam('adsense.ad_client');

        $content = $this->container->get('edemy.main')->render('templates/main/google/adsense',array(
            'ad_client' => $ad_client,
            'ad_slot'  => $ad_slot,
        ));
        //die(var_dump($content));

        return $content;
    }

    public function getName()
    {
        return 'edemy_google_extension';
    }
}
