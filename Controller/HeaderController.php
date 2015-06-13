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
use Symfony\Component\HttpFoundation\Response;

/**
 * HeaderController
 *
 * El servicio edemy.header es el encargado de generar el header en la respuesta.

 * @author Manuel Sanchís <msanchis@edemy.es>
 */
class HeaderController extends BaseController
{
    /**
     * @return array Subscribed Events List
     */
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_header_lastmodified'     => array('onHeaderLastModified', 0),
            'edemy_header'                  => array('onHeader', 0),
        ));
    }

    /**
     * Este listener calcula lastmodified del header
     *
     * @param ContentEvent $event
     * @return bool
     */
    public function onHeaderLastModified(ContentEvent $event)
    {
//        $this->dispatch('edemy_header_module_lastmodified', $event);
//        $lastmodified = $event->getLastModified();
//        $lastmodified_files = $this->getLastModifiedFiles('/../../*/Resources/views', 'templates/header.html.twig');
//        if($lastmodified_files > $lastmodified) {
//            $lastmodified = $lastmodified_files;
//        }
//        $event->setLastModified($lastmodified);
    }

    /**
     * Este listener une los módulos de header que se han generado
     * con el evento edemy_header_module.
     * La template que utiliza para esta función es snippets/header_join.html.twig
     *
     * @param ContentEvent $event
     * @param $eventName
     * @param EventDispatcherInterface $dispatcher
     * @return bool
     */
    public function onHeader(ContentEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        //$header = null;
        $event->clearModules();
        $dispatcher->dispatch('edemy_header_module', $event);
        $event->setHeader(
            $this->render("templates/header", array(
                'modules' => $event->getModules(),
            ))
        );

        return true;
    }

    public function header($_route = null)
    {
        $event = new ContentEvent($_route);
        $this->dispatch('edemy_header_lastmodified', $event);
        $lastmodified = $event->getLastModified();
        $response = new Response();

        if($lastmodified != null) {
            $response->setLastModified($lastmodified);
            //$response->setPublic();
            if($response->isNotModified($this->getRequest())) {
                return $response;
            }
        }
        $this->dispatch('edemy_header', $event);
        $response->setContent($event->getHeader());
        return $response;
    }
}
