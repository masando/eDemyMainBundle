<?php

/*
 * This file is part of the eDemy Framework package.
 *
 * (c) Manuel Sanchís <msanchis@edemy.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace eDemy\MainBundle\Controller;

use eDemy\MainBundle\Event\ContentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * MenuController
 *
 * El servicio edemy.menu es el encargado de generar los menus.

 * @author Manuel Sanchís <msanchis@edemy.es>
 */
class MenuController extends BaseController
{
    private $paramController;

    /**
     * @return array Subscribed Events List
     */
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_header_module'       => array('onHeaderModule', 0),
            'edemy_footer_module'       => array('onFooterModule', 0),
        ));
    }

    /**
     * @param $paramController
     */
    public function setParamController($paramController)
    {
        $this->paramController = $paramController;
    }

    /**
     * @param null $namespace
     */
    public function adminMenu($namespace = null)
    {
        $response = $this->newResponse();

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $lastmodified = $this->get('doctrine.orm.entity_manager')->getRepository('eDemyMainBundle:Param')->findLastAdminMenuModified($this->getNamespace())->getUpdated();
            $response->setPrivate();
            if($lastmodified) {
                $response->setLastModified($lastmodified);
                if($response->isNotModified($this->getRequest())) {
                    return $response;
                }
            }

            $menuEvent = new GenericEvent("menu", array(
                'items' => array(),
                'namespace' => $namespace,
            ));
            $this->get('event_dispatcher')->dispatch('edemy_main_adminmenu', $menuEvent);
            if(count($menuEvent['items'])) {
                $response = $this->newResponse($this->render('admin/adminmenu', array(
                    'menu' => 'admin',
                    'items' => $menuEvent['items'],
                )));

                //$response->setPublic();
                //$response->setMaxAge(300);
                //$response->setVary('Cookie', false);
                //$response->setSharedMaxAge(300);
                //$response->headers->addCacheControlDirective('must-revalidate', true);
            }
        }
        
        return $response;
    }

    /**
     * @param ContentEvent $event
     * @param $eventName
     * @param EventDispatcherInterface $dispatcher
     * @return bool
     */
    public function onHeaderModule(ContentEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        //die("b");
        /*
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            $menuEvent = new GenericEvent(
                "menu",
                array(
                    'items' => array(),
                )
            );

            $dispatcher->dispatch('edemy_main_adminmenu', $menuEvent);
            //die(var_dump($menuEvent['items']));
            $event->addModule($this->render('menu.html.twig',
                array(
                    'menu' => 'admin',
                    'items' => $menuEvent['items'],
                )
            ));
        }
        */
        $menuEvent = new GenericEvent("menu", array(
            'items' => array(),
        ));
        //die(var_dump($options));
//        $event = new GenericEvent(
//            "mainmenu",
//            array('name' => '')
//        );
        $dispatcher->dispatch('edemy_mainmenu', $menuEvent);

//        die(var_dump($event['mainmenu']));

        $dispatcher->dispatch('edemy_main_mainmenu', $menuEvent);
//die(var_dump($menuEvent['items']));
        $event->addModule($this->render('templates/menu', array(
            'namespace' => $this->getNamespace(),
            'menu' => 'main',
            'items' => $menuEvent['items'],
        )));

        return true;
    }

    /**
     * @param ContentEvent $event
     * @param $eventName
     * @param EventDispatcherInterface $dispatcher
     * @return bool
     */
    public function onFooterModule(ContentEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $menuEvent = new GenericEvent(
            "menu",
            array(
                'items' => array(),
            )
        );

        $dispatcher->dispatch('edemy_main_footermenu', $menuEvent);

        $this->addEventModule($event, 'templates/menu', array(
            'menu' => 'footer',
            'items' => $menuEvent['items'],
        ));

        return true;
    }
}
