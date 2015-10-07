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

    public function mainSitemapAction()
    {
        $prefixes = $this->getParamByType('prefix');

        $content = $this->render(
            "templates/main/sitemap/main",
            array(
                'prefixes' => $prefixes,
            )
        );

        $response = new Response($content);
        $response->headers->set('Content-type', 'text/xml');

        return $response;
    }

    public function sitemapAction()
    {
        $event = new ContentEvent();
        $event->clearModules();

        $this->eventDispatcher->dispatch('edemy_sitemap_module', $event);

        $content = $this->render("templates/main/sitemap/sitemap", array(
            'modules' => $event->getModules(),
        ));

        $response = new Response($content);
        $response->headers->set('Content-type', 'text/xml');

        return $response;
    }

}
