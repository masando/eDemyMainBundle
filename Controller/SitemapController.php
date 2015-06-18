<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use eDemy\MainBundle\Controller\BaseController;
use eDemy\MainBundle\Event\ContentEvent;

class SitemapController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main');
    }

    public function indexAction()
    {
        $event = new ContentEvent();
        $event->clearModules();
        $this->eventDispatcher->dispatch('edemy_sitemap_module', $event);
        //die(var_dump($event->getModules()));
        $content = $this->render("templates/main/sitemap/sitemap", array(
            'modules' => $event->getModules(),
        ));
        $response = new Response($content);
        $response->headers->set('Content-type', 'text/xml');

        return $response;
    }

}
