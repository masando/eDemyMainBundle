<?php

namespace eDemy\MainBundle\Controller;

use eDemy\MainBundle\Event\ContentEvent;

class MainController extends BaseController
{
    /** @var $event ContentEvent */
    protected $event;

    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_main_frontpage'                  => array('onFrontpage', 0),
            'edemy_main_frontpage_lastmodified'     => array('onFrontpageLastModified', 0),
            'edemy_footer_module'                   => array('onFooterModule', 0),
        ));
    }

    public function indexAction($_format = 'html')
    {
        $this->event = new ContentEvent($this->getRouteWithoutNamespace());
        $this->event->setFormat($_format);
        // Si no ha cambiado el contenido devolvemos 304
        if($response = $this->contentNotModified()) {
            return $response;
        }
        // Si hemos detenido la propagación del evento, devolvemos el contenido sólamente
        if($response = $this->isPropagationStopped()) {
            return $response;
        }
//        die(var_dump($this->event->getContent()));
        // Full Response
        if($response = $this->getFullResponse()) {
            return $response;
        }

        return null;
    }

    public function contentNotModified() {
        //$this->start($event->getRoute(), 'lastmodified');
        $stopwatch = $this->get('debug.stopwatch');
        $crono = $stopwatch->start('contentNotModified');

        //obtenemos lastmodified de la ruta y de los ficheros principales para devolver 304
        if ($this->dispatch($this->event->getRouteLastModified(), $this->event)) {
            $lastmodified = $this->event->getLastModified();
            $this->getBundlePath($this->getParam("themeBundle", null, $this->getBundleName()));
            $lastmodified_files = $this->getLastModifiedFiles(
                //$this->getParam("themeBundle", null, $this->getBundleName())
                '/vendor/edemy/mainbundle/eDemy/MainBundle/Resources/views',
                '*.html.twig'
            );
            if ($lastmodified_files > $lastmodified) {
                $lastmodified = $lastmodified_files;
                $this->event->setLastModified($lastmodified);
            }

            if ($response = $this->ifNotModified304($lastmodified)) {
                return $response;
            }
        }
        $crono->stop();

        return false;
    }

    public function isPropagationStopped() {
        $stopwatch = $this->get('debug.stopwatch');
        $crono = $stopwatch->start('isPropagationStopped');
        $lastmodified = $this->event->getLastModified();
        //si hay que generar la respuesta, primero obtenemos el contenido principal
        $event = new ContentEvent($this->event->getRoute());
        $this->dispatch('edemy_content', $event);
        $this->event->setContent($event->getContent());
//        die(var_dump($content));


        //si se ha detenido la propagación devolvemos la respuesta inmediatamente
        if ($this->event->isPropagationStopped()) {
            $response = $this->newResponse($this->event->getContent());
            $response->setLastModified($lastmodified);
//die('a');
            //$response->setPublic();

            return $response;
        }
        $crono->stop();

        return false;
    }

    public function getFullResponse() {
        $stopwatch = $this->get('debug.stopwatch');
        $crono = $stopwatch->start('fullResponse');
        // En los demás casos, decoramos la respuesta
        $this->dispatch('edemy_meta_title', $this->event);
        $title = $this->event->getTitle();
        $this->dispatch('edemy_meta_description', $this->event);
        $description = $this->event->getDescription();
        $this->dispatch('edemy_meta_keywords', $this->event);
        $keywords = $this->event->getKeywords();
        $this->dispatch('edemy_meta', $this->event);
        $meta = $this->event->getMeta();

        if ($this->event->getMode() == 'compact') {
            $header = null;
            $footer = null;
        } else {
            //$header = $this->dispatch('edemy_header', $event)->getHeader();
            //$footer = $this->dispatch('edemy_footer', $event)->getFooter();
        }
//        $this->start('layout.html', 'render');
        //try {
        // @TODO use param to set the bundle that has the theme
        $response = $this->get('templating')->renderResponse(
            $this->getTemplate('layout/theme', $this->event->getFormat()),
            array(
                'title' => $title,
                'description' => $description,
                'keywords' => $keywords,
                'meta' => $meta,
                //'header'      => $header,
                'content' => $this->event->getContent(),
                //'footer'      => $footer,
                'namespace' => $this->getNamespace(),
            )
        );
        //} catch (\Exception $e) {
        //return new RedirectResponse($this->getRequest()->getUri());

//        }

//        $resp = new Response($response);
//        $response = $resp;
//        $this->stop('layout.html');
        if ($lastmodified = $this->event->getLastmodified()) {
            $response->setLastModified($lastmodified);
        }
        $response->setPublic();
        $crono->stop();

        return $response;
    }

    public function ifNotModified304(\DateTime $lastmodified) {
        $response = $this->newResponse();
        $response->setLastModified($lastmodified);
        $response->setPublic();
        if ($response->isNotModified($this->getRequest())) {

            return $response;
        }

        return false;
    }

    // EVENT LISTENERS
    public function onFrontpageLastModified(ContentEvent $event)
    {
        $e = new ContentEvent();
        $frontpage_route = $this->getParam('frontpage');
        if($frontpage_route != 'frontpage') {
            $e->setRoute($frontpage_route.'_lastmodified');
            $this->dispatch($frontpage_route.'_lastmodified', $e);
            $lastmodified = $e->getLastModified();
        } else {
            $lastmodified = null;
        }
        $lastmodified_files = $this->getLastModifiedFiles('/vendor/edemy/mainbundle/eDemy/MainBundle/Resources/views/layout', 'theme.html.twig');
        if($lastmodified_files > $lastmodified) {
            $lastmodified = $lastmodified_files;
        }

        $event->setLastModified($lastmodified);
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
            $this->render("frontpage", array(
                'modules' => $event->getModules(),
            ))
        );

        return true;
    }

    public function onFooterModule(ContentEvent $event)
    {
        $namespaces = $this->getParamByType('prefix');
        
        $this->addEventModule($event, "footer_module", array(
            'namespaces' => $namespaces,
        ));
    }
}
