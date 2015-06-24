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

use Symfony\Component\EventDispatcher\GenericEvent;
use eDemy\MainBundle\Entity\Param;
use eDemy\MainBundle\Event\ContentEvent;

/**
 * MainController
 *
 * El servicio edemy.main es el encargado de generar la respuesta a partir del nombre de la ruta.
 * Obtiene información acerca de la última modificación para poder devolver una respuesta 304
 * o una respuesta completa.

 * @author Manuel Sanchís <msanchis@edemy.es>
 */
class MainController extends BaseController
{
    /**
     * @return array Subscribed Events List
     */
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_main_frontpage_lastmodified'     => array('onFrontpageLastModified', 0),
            'edemy_main_frontpage'                  => array('onFrontpage', 0),
            'edemy_footer_module'                   => array('onFooterModule', 0),
            'edemy_mainmenu'                        => array('onFrontpageMainMenu', 100),
        ));
    }

    public function onFrontpageMainMenu(GenericEvent $menuEvent) {
        $items = array();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $item = new Param($this->get('doctrine.orm.entity_manager'));
            $item->setName('Admin');
            $item->setOrden(100);
            $items[] = $item;
        }
        $item = new Param($this->get('doctrine.orm.entity_manager'));
        $item->setName('Inicio');
        if($namespace = $this->getNamespace()) {
            $namespace .= ".";
        }
        $item->setValue($namespace . 'edemy_main_frontpage');

        $items[] = $item;

        $menuEvent['items'] = array_merge($menuEvent['items'], $items);

        return true;
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
    public function indexAction($_format = 'html')
    {
        $event = new ContentEvent($this->getRouteWithoutNamespace());
        $event->setFormat($_format);
        $this->dump($event->getRoute());

        if($lastmodified = $this->getLastModified($event->getRoute(), $event->getFormat())) {
//            if ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
//                $response->setPrivate();
//            } else {
//                $response->setPublic();
//            }
            $referer = $this->getRequest()->headers->get('referer');

            $Pattern = "/logout$/";

            if(preg_match($Pattern, $referer, $matches))
            {
                $lastmodified = new \DateTime();
            }
            $lastmodified = new \DateTime();


            $event->setLastModified($lastmodified);
            $this->dump($lastmodified);

            if ($response = $this->ifNotModified304($lastmodified)) {
//                $response->headers->addCacheControlDirective( 'no-cache', true );
//                $response->headers->addCacheControlDirective( 'max-age', 0 );
//                $response->headers->addCacheControlDirective( 'must-revalidate', true );
//                $response->headers->addCacheControlDirective( 'no-store', true );
//                $response->setPrivate();

                return $response;
            }
        }
        if($content = $this->getContent($event->getRoute())) {
            $event->setContent($content);
        }
//        die(var_dump($content));
        if(gettype($content) == 'object') {
            if (get_class($content) == 'Symfony\Component\HttpFoundation\RedirectResponse') {

                return $content;
            }
            if (get_class($content) == 'Symfony\Component\HttpFoundation\Response') {

                return $content;
            }
        }
//        die(var_dump($event));
        // @TODO comprobar stopPropagation
        if($response = $this->isPropagationStopped($event)) {

            return $response;
        }
        if($response = $this->getFullResponse($event)) {
            $this->get('session')->set('out', '0');

            return $response;
        }

        return null;
    }

    /**
     * Este listener calcula lastmodified de la ruta edemy_main_frontpage
     * Para ello calcula el lastmodified de la ruta que se muestra en frontpage (si existe)
     * o toma el de $event si no existe (que por ahora es null)
     *
     * @param ContentEvent $event
     * @return bool
     */
    public function onFrontpageLastModified(ContentEvent $event)
    {
        $e = new ContentEvent();
        $frontpageRoute = $this->getParam('frontpage');
        if($frontpageRoute != 'frontpage') {
            $e->setRoute($frontpageRoute.'_lastmodified');
            $this->dispatch($frontpageRoute.'_lastmodified', $e);
            $lastmodified = $e->getLastModified();
        } else {
            $lastmodified = $event->getLastModified();
        }

        $event->setLastModified($lastmodified);

        return true;
    }

    /**
     * Este listener escucha el evento edemy_main_frontpage y es el encargado de sustituir el frontpage
     * por otra ruta, definida en el Param frontpage
     * (y edemy_main_frontpage_mode para el modo de visualización),
     * o de devolver el contenido del archivo frontpage.html.twig
     * Si está definida se lanza un evento para generar la respuesta.
     * Añade la funcionalidad de parar la propagación o de unir las diferentes subrespuestas en una sóla.
     *
     * @param ContentEvent $event
     * @return bool
     */
    public function onFrontpage(ContentEvent $event)
    {
        if(($frontpageRoute = $this->getParam('frontpage')) !== 'frontpage') {
            if ($this->getParam('edemy_main_frontpage_mode') != 'edemy_main_frontpage_mode') {
                $event->setMode($this->getParam('edemy_main_frontpage_mode'));
            }

            $event->setRoute($frontpageRoute);
            $this->eventDispatcher->dispatch($frontpageRoute, $event);
//            die(var_dump($event));
            if ($event->isPropagationStopped()) {
//die();
                return false;
            }
            $event->setContent(
                $this->render("snippets/join", array(
                    'modules' => $event->getModules(),
                ))
            );

            return true;
        } else {
            $event->addModule(
                $this->render("templates/main/index")
            );

            return true;
        }
    }

    /**
     * @param ContentEvent $event
     * @return bool
     */
    public function onFooterModule(ContentEvent $event)
    {
        $namespaces = $this->getParamByType('prefix');
        
        $this->addEventModule($event, "templates/main/footer_module", array(
            'namespaces' => $namespaces,
        ));

        return true;
    }
}
