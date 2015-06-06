<?php

/*
 * This file is part of the eDemy Framework package.
 *
 * (c) Manuel Sanchís <msanchis@edemy.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * JavascriptController
 *
 * El servicio edemy.js es el encargado de generar los scripts asociados a la ruta.

 * @author Manuel Sanchís <msanchis@edemy.es>
 */
namespace eDemy\MainBundle\Controller;

use eDemy\MainBundle\Event\ContentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Finder\Finder;

class JsController extends BaseController
{
    /**
     * @return array Subscribed Events List
     */
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_javascript_lastmodified' => array('onJavascriptLastModified', 0),
            'edemy_javascript'              => array('onJavascript', 0),
        ));
    }

    /**
     * Éste es el punto de llegada de la mayoría de los requests
     * A partir de la ruta se genera la respuesta en varias fases con ayuda de un ContentEvent.
     * Si la respuesta no ha variado se devuelve un response 304.
     * Si la propagación del evento se ha parado, se devuelve sólo el contenido principal de la ruta.
     * En los demás casos se generan todos los demás elementos asociados a la ruta (título, descripción...)
     *
     * @param string $_format
     * @return bool|null|\Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($_format = 'css')
    {
        //die(var_dump($this->get('templating.globals')));
/*        $parts = explode('.', $_route);
        if(count($parts) == 2) {
            $_route = end($parts);
        }

        $event = new ContentEvent($_route);
*/
        $event = new ContentEvent($this->getRouteWithoutNamespace());
        $event->setFormat($_format);

        if($lastmodified = $this->getLastModified($event->getRoute(), $event->getFormat())) {
            $event->setLastModified($lastmodified);
            if ($response = $this->ifNotModified304($lastmodified)) {

                return $response;
            }
        }

        $js = $this->getJs($event->getRoute());
        $response = $this->newResponse();
        $response->setContent($js);
        $response->headers->set('Content-Type', 'text/javascript');
        $response->setPublic();
        $response->setLastModified($lastmodified);

        return $response;

        //aquí Last-Modified Header
//        $lastmodified = $this->dispatch('edemy_javascript_lastmodified', $event)->getLastModified();

/*        if($lastmodified != null) {
            //die();
            $response = new Response();
            $response->setLastModified($lastmodified);
            $response->setPublic();
            //die(var_dump($this->getRequest()));
            //die(var_dump($lastmodified));
            //die(var_dump($response->isNotModified($this->getRequest())));

            if ($response->isNotModified($this->getRequest())) {
                //die(var_dump($response));
                //die();

                return $response;
            }
        }

        $javascript = $this->dispatch('edemy_javascript', $event)->getJavascript();
        $response = $this->newResponse();
        $response->setContent($javascript);
        $response->headers->set('Content-Type', 'text/javascript');

        if($lastmodified != null) {
            $response->setLastModified($lastmodified);
        }

        if($event->getLastModified() != null) {
            $response->setLastModified($event->getLastModified());
        }
        $response->setPublic();

        return $response;*/

        /*
        $allparams = $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName().':Param')->findAll();
        foreach($allparams as $param) {
            $params[$param->getName()] = $param->getValue();
        }
        $response = $this->newResponse();
        $response->setContent($this->get('templating')->render(
            $this->getBundleName().':Css:'.$file.'.css.twig',
            array(
                'params' => $params
            )
        ));
        $response->headers->set('Content-Type', 'text/css');
        return $response;
        * */
    }

    /**
     * Este listener calcula lastmodified de la ruta
     *
     * @param ContentEvent $event
     * @return bool
     */
    public function onJavascriptLastModified(ContentEvent $event)
    {
        $reflection = new \ReflectionClass(get_class($this));
        $dir = dirname($reflection->getFileName());

        if(get_class($this) != "eDemy\MainBundle\Controller\MainController") {
            //die(var_dump(get_class($this)));
            //die(var_dump($dir));
        }


        $finder = new Finder();
        $finder
            ->files()
            ->in($dir . '/../../*/Resources/views/js')
            ->sortByModifiedTime();

        foreach ($finder as $file) {
            //print $file->getRealpath()."-";
            //print date($file->getMTime())."<br/>";
//            $lastmodified = new \DateTime();
            $lastmodified = \DateTime::createFromFormat( 'U', $file->getMTime() );
            //$formattedString = $currentTime->format( 'c' );
            //die(var_dump($currentTime));
            //obtener la última fecha de modificación
            //$lastmodified = ;
            if($event->getLastModified() > $lastmodified) {
                //$event->setLastModified($lastmodified);
            } else {
                $event->setLastModified($lastmodified);
            }
        }
        //die();
        //print date($file->getMTime())."<br/>";
        //die(var_dump($event->getLastModified()));
    }

    /**
     * Este listener une los módulos de js que se han generado
     * con el evento edemy_js.
     * La template que utiliza para esta función es snippets/js_join.html.twig
     *
     * @param ContentEvent $event
     * @param $eventName
     * @param EventDispatcherInterface $dispatcher
     * @return bool
     */
    public function onJavascript(ContentEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $event->clearModules();
        $dispatcher->dispatch('edemy_javascript_module', $event);

        $event->setJavascript(
            $this->render("snippets/javascript_join", array(
                'modules' => $event->getModules()
            ))
        );

        return true;
    }
}
