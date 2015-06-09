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

use eDemy\MainBundle\Controller\BaseController;
use eDemy\MainBundle\Event\ContentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * FooterController
 *
 * El servicio edemy.footer es el encargado de generar el footer en la respuesta.

 * @author Manuel Sanchís <msanchis@edemy.es>
 */
class FooterController extends BaseController
{
    /**
     * @return array Subscribed Events List
     */
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_footer_lastmodified'     => array('onFooterLastModified', 0),
            'edemy_footer'                  => array('onFooter', 0),
        ));
    }

    /**
     * Este listener calcula lastmodified del footer
     *
     * @param ContentEvent $event
     * @return bool
     */
    public function onFooterLastModified(ContentEvent $event)
    {
//        $lastmodified = $this->dispatch('edemy_footer_module_lastmodified', $event)->getLastModified();
//        $lastmodified_files = $this->getLastModifiedFiles('/../../*/Resources/views', 'footer_module.html.twig');
//        if($lastmodified_files > $lastmodified) {
//            $lastmodified = $lastmodified_files;
//        }
//        $event->setLastModified($lastmodified);
    }

    /**
     * Este listener une los módulos de css que se han generado
     * con el evento edemy_footer_module.
     * La template que utiliza para esta función es snippets/css_join.html.twig
     *
     * @param ContentEvent $event
     * @param $eventName
     * @param EventDispatcherInterface $dispatcher
     * @return bool
     */
    public function onFooter(ContentEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        if($this->getParam('footer.enable') == 1) {
            $event->clearModules();
            $dispatcher->dispatch('edemy_footer_module', $event);
            $copyright = $this->get('edemy.document')->getDocument('copyright');
            $event->setFooter(
                $this->render("snippets/footer_join", array(
                    'modules' => $event->getModules(),
                    'copyright' => $copyright,
                ))
            );
        }

        return true;
    }

    /**
     * @param null $_route
     * @param Request $request
     * @return Response
     */
    public function footer($_route = null, Request $request)
    {
        $event = new ContentEvent($_route);
        $this->dispatch('edemy_footer_lastmodified', $event);
        $lastmodified = $event->getLastModified();
        $response = new Response();
        if($lastmodified != null) {
            $response->setLastModified($lastmodified);
            if($response->isNotModified($request)) {
                return $response;
            }
        }
        $response->setPublic();
        $response->setContent($this->dispatch('edemy_footer', $event)->getFooter());

        return $response;
    }
}
