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
//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;

/**
 * CssController
 *
 * El servicio edemy.css es el encargado de generar la hoja de estilos asociada a la ruta.

 * @author Manuel Sanchís <msanchis@edemy.es>
 */
class CssController extends BaseController
{
    /**
     * @return array Subscribed Events List
     */
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_css_lastmodified'    => array('onCssLastModified', 0),
            'edemy_css'                 => array('onCss', 0),
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
        $event = new ContentEvent($this->getRouteWithoutNamespace());
        $event->setFormat($_format);

        if($lastmodified = $this->getLastModified($event->getRoute(), $event->getFormat())) {
            $event->setLastModified($lastmodified);
            if ($response = $this->ifNotModified304($lastmodified)) {

                return $response;
            }
        }
        $css = $this->getCss($event->getRoute());
        $response = $this->newResponse();
        $response->setContent($css);
        $response->headers->set('Content-Type', 'text/css');
        $response->setPublic();
        $response->setLastModified($lastmodified);

        return $response;
    }

    /**
     * Este listener calcula lastmodified de la ruta
     *
     * @param ContentEvent $event
     * @return bool
     */
    public function onCssLastModified(ContentEvent $event)
    {
        // @TODO lastmodified files from themeBundle templates
        $reflection = new \ReflectionClass(get_class($this));
        // @TODO
        if(strpos($reflection->getFileName(), 'app/cache/')) {
            $dir = dirname($reflection->getFileName()) . '/../../..';
        } else {
            $dir = dirname($reflection->getFileName()) . '/../../../../../..';
        }

        if(get_class($this) != "eDemy\MainBundle\Controller\MainController") {
        }


        $finder = new Finder();
        // @TODO
        $finder
            ->files()
            ->in($dir . '/vendor/edemy/mainbundle/eDemy/*/Resources/views/assets')
            ->name('*.css.twig')
            ->sortByModifiedTime();

        foreach ($finder as $file) {
            //$lastmodified = new \DateTime();
            $lastmodified = \DateTime::createFromFormat( 'U', $file->getMTime() );
            //$formattedString = $currentTime->format( 'c' );
            if($event->getLastModified() > $lastmodified) {
                //$event->setLastModified($lastmodified);
            } else {
                $event->setLastModified($lastmodified);
            }
        }
    }

    /**
     * Este listener une los módulos de css que se han generado
     * con el evento edemy_css.
     * La template que utiliza para esta función es snippets/css_join.html.twig
     *
     * @param ContentEvent $event
     * @param $eventName
     * @param EventDispatcherInterface $dispatcher
     * @return bool
     */
    public function onCss(ContentEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $css = null;
        $event->clearModules();
        if($dispatcher->dispatch('edemy_css_module', $event)->isPropagationStopped()) {

            return false;
        }
        $event->setCss(
            $this->render("snippets/css_join", array( 'modules' => $event->getModules() ))
        );

        return true;
    }
}