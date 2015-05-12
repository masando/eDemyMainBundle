<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Component\DependencyInjection\ContainerInterface;
use eDemy\MainBundle\Controller\BaseController;

class ServiceContainerController extends BaseController
{
    protected $container;

    public static function getSubscribedEvents()
    {
        //return self::getSubscriptions('service', [], array(
        //    'edemy_service'       => array('onService', 0),
        //));
        return array(
            'edemy_service' => array('onService', 0),
        );
    }

    public function onService($event)
    {
        $event['service'] = $this->container->get($event['name']);
        
        return true;
    }

    public function setContainer(ContainerInterface $container = NULL)
    {
        $this->container = $container;
    }
}
