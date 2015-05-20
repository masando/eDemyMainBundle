<?php

namespace eDemy\MainBundle\Controller;

use eDemy\MainBundle\Event\ContentEvent;

class MainController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_main_frontpage'       => array('onFrontpage', 0),
            'edemy_main_frontpage_lastmodified'       => array('onFrontpageLastModified', 0),
            'edemy_footer_module'       => array('onFooterModule', 0),
        ));
    }

    public function onFrontpageLastModified(ContentEvent $event)
    {
        $frontpage_route = $this->getParam('frontpage');

        if($frontpage_route != null) {
            $event->setRoute($frontpage_route . '_lastmodified');
            $this->dispatch($frontpage_route . '_lastmodified', $event);
            $lastmodified = $event->getLastModified();

            $lastmodified_files = $this->getLastModifiedFiles('/vendor/edemy/mainbundle/Resources/views', 'layout.html.twig');
            if($lastmodified_files > $lastmodified) {
                $lastmodified = $lastmodified_files;
            }
            $event->setLastModified($lastmodified);
        }
    }

    public function onFrontpage(ContentEvent $event)
    {
        $frontpage_route = $this->getParam('frontpage');
        if($this->getParam('edemy_main_frontpage_mode') != 'edemy_main_frontpage_mode') {
            $event->setMode($this->getParam('edemy_main_frontpage_mode'));
        }

        $event->setRoute($frontpage_route);
        $this->eventDispatcher->dispatch($frontpage_route, $event);
        if($event->isPropagationStopped()) {
            return false;
        }
        $event->setContent(
            $this->render("eDemyMainBundle::frontpage.html.twig", array(
                'modules' => $event->getModules(),
            ))
        );

        return true;
    }

    public function indexAction($_route, $_format = 'html')
    {
        //error_log($this->get('kernel')->getLog());
        return $this->renderResponse($_route, $_format);
    }

    public function onFooterModule(ContentEvent $event)
    {
        $namespaces = $this->getParamByType('prefix');
        
        $this->addEventModule($event, "footer_module.html.twig", array(
            'namespaces' => $namespaces,
        ));
    }
}
