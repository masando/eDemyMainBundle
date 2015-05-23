<?php

namespace eDemy\MainBundle\Controller;

use eDemy\MainBundle\Event\ContentEvent;

class MainController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_main_frontpage'                  => array('onFrontpage', 0),
            'edemy_main_frontpage_lastmodified'     => array('onFrontpageLastModified', 0),
            'edemy_footer_module'                   => array('onFooterModule', 0),
        ));
    }

    public function indexAction($_route, $_format = 'html')
    {
        //error_log($this->get('kernel')->getLog());
        //return $this->renderResponse($_route, $_format);
        //$namespace = $this->getNamespace();
        //if($namespace) $_route = $namespace . '.' . $_route;


        dump('_route_namespace: ' . $_route);
        // Obtenemos namespace y _route a partir de la ruta inicial
//        $namespace = $this->getNamespaceFromRoute($_route);
        $namespace = $this->getNamespace();
        dump('Namespace: ' . $namespace);
        $_route = $this->getRouteWithoutNamespace();
        dump('_route: ' . $_route);

        //obtenemos lastmodified de la ruta y de los ficheros principales para devolver 304
        $lastmodified = null;
        $event = new ContentEvent($_route);
        $this->start($_route.'lastmodified', 'lastmodified');
        if ($this->dispatch($_route.'_lastmodified', $event)) {
            $lastmodified = $event->getLastModified();
            //die(var_dump($_route));
            if ($lastmodified) {
                $lastmodified_files = $this->getLastModifiedFiles(
                    '/vendor/edemy/mainbundle/eDemy/MainBundle/Resources/views',
                    '*.html.twig'
                );
                if ($lastmodified_files > $lastmodified) {
                    $lastmodified = $lastmodified_files;
                }
                $response = $this->newResponse();
                $response->setLastModified($lastmodified);
                $response->setPublic();
                if ($response->isNotModified($this->getRequest())) {

                    return $response;
                }
            }
        }

        $this->stop($_route.'lastmodified', 'lastmodified');

        //si hay que generar la respuesta, primero obtenemos el contenido principal
        $event = new ContentEvent($_route);
        $this->dispatch('edemy_content', $event);
        $content = $event->getContent();
        //die(var_dump($content));
        //si se ha detenido la propagación devolvemos la respuesta inmediatamente
        if ($event->isPropagationStopped()) {
            $response = $this->newResponse($content);
            $response->setLastModified($lastmodified);

            //$response->setPublic();

            return $content;
        }
        //si no, creamos la respuesta completa
        $this->dispatch('edemy_meta_title', $event);
        $title = $event->getTitle();
        $this->dispatch('edemy_meta_description', $event);
        $description = $event->getDescription();
        $this->dispatch('edemy_meta_keywords', $event);
        $keywords = $event->getKeywords();
        $this->dispatch('edemy_meta', $event);
        $meta = $event->getMeta();

        if ($event->getMode() == 'compact') {
            $header = null;
            $footer = null;
        } else {
            //$header = $this->dispatch('edemy_header', $event)->getHeader();
            //$footer = $this->dispatch('edemy_footer', $event)->getFooter();
        }
        //die(var_dump($content));
//        $this->start('layout.html', 'render');
//die(var_dump($this->getBundleName() . '::' . $this->getParam("theme", null, "layout") . '.' . $_format . '.twig'));
        //try {
        // @TODO use param to set the bundle that has the theme
        $response = $this->get('templating')->renderResponse(
            $this->getTemplate('layout/theme', $_format),
            array(
                'title' => $title,
                'description' => $description,
                'keywords' => $keywords,
                'meta' => $meta,
                //'header'      => $header,
                'content' => $content,
                //'footer'      => $footer,
                'namespace' => $namespace,
            )
        );
        //} catch (\Exception $e) {
        //die(var_dump($e));
        //return new RedirectResponse($this->getRequest()->getUri());

        //die(var_dump($e));
//        }

//        $resp = new Response($response);
//        $response = $resp;
        //die(var_dump($response));
//        $this->stop('layout.html');
        if ($lastmodified) {
            $response->setLastModified($lastmodified);
        }
        $response->setPublic();

        return $response;
    }

    public function onFrontpageLastModified(ContentEvent $event)
    {
        $frontpage_route = $this->getParam('frontpage');
        //die(var_dump($frontpage_route));
        if($frontpage_route != null) {
            $event->setRoute($frontpage_route . '_lastmodified');
            $this->dispatch($frontpage_route . '_lastmodified', $event);
            $lastmodified = $event->getLastModified();

            $lastmodified_files = $this->getLastModifiedFiles('/vendor/edemy/mainbundle/eDemy/MainBundle/Resources/views', 'layout.html.twig');
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
            $this->render($this->getTemplate("frontpage"), array(
                'modules' => $event->getModules(),
            ))
        );

        return true;
    }

    public function onFooterModule(ContentEvent $event)
    {
        $namespaces = $this->getParamByType('prefix');
        
        $this->addEventModule($event, "footer_module.html.twig", array(
            'namespaces' => $namespaces,
        ));
    }
}
