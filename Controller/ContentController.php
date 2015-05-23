<?php

namespace eDemy\MainBundle\Controller;

//use eDemy\MainBundle\Controller\BaseController;
use eDemy\MainBundle\Event\ContentEvent;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContentController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_content' => array('onContent', 0),
            'edemy_content_lastmodified'     => array('onContentLastModified', 0),
        ));
    }

    public function onContentLastModified(ContentEvent $event)
    {
        $lastmodified = $this->dispatch('edemy_content_module_lastmodified', $event)->getLastModified();
        $lastmodified_files = $this->getLastModifiedFiles('/../../*/Resources/views', 'content.html.twig');
        if($lastmodified_files > $lastmodified) {
            $lastmodified = $lastmodified_files;
        }
        $event->setLastModified($lastmodified);
    }

    public function onContent(ContentEvent $event)
    {
        $parts = explode('.', $event->getRoute());
        if(count($parts) == 2) {
            $_route = end($parts);
        } else {
            $_route = $event->getRoute();
        }
        //die(var_dump($event->getRoute()));
        $event->setRoute($_route);
        $content = null;
        $event->clearModules();
        $this->eventDispatcher->dispatch('edemy_precontent_module', $event);
        $this->eventDispatcher->dispatch($event->getRoute(), $event);
        if($event->isPropagationStopped()) {
            return false;
        }
        $this->eventDispatcher->dispatch('edemy_postcontent_module', $event);
        $event->setContent(
            $this->render("content.html.twig", array(
                'modules' => $event->getModules()
            ))
        );
        return true;
    }

    public function content($_path = null, Request $request)
    {
        $route = $this->get('router')->match($_path);
        $event = new ContentEvent($route['_route'], $route);
        $lastmodified = $this->dispatch('edemy_content_lastmodified', $event)->getLastModified();
        $event = new ContentEvent($route['_route'], $route);
        $response = new Response();
        if($lastmodified) {
            $response->setLastModified($lastmodified);
            $response->setPublic();
            if($response->isNotModified($this->getRequest())) {
                return $response;
            }
        }
        $response->setContent($this->dispatch('edemy_content', $event)->getContent());

        return $response;
    }
}
