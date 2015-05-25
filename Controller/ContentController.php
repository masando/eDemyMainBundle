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

/**
 * ContentController
 *
 * El servicio edemy.content es el encargado de lanzar los eventos necesarios para generar
 * el contenido asociado a una ruta.
 * Los listeners añaden sus módulos al evento que se lanza y se unen a través de la
 * template snippets/content.html.twig

 * @author Manuel Sanchís <msanchis@edemy.es>
 */
class ContentController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_content'                 => array('onContent', 0),
        ));
    }

    /**
     * Este listener une los módulos de contenido que se han generado
     * con el evento edemy_content, pre y post.
     * La template que utiliza para esta función es snippets/content_join.html.twig
     *
     * @param ContentEvent $event
     * @return bool
     */
    public function onContent(ContentEvent $event)
    {
        $content = null;
        $event->clearModules();
        $this->eventDispatcher->dispatch('edemy_precontent_module', $event);
        $this->eventDispatcher->dispatch($event->getRoute(), $event);
        if($event->isPropagationStopped()) {

            return false;
        }
        $this->eventDispatcher->dispatch('edemy_postcontent_module', $event);
        $event->setContent(
            $this->render("snippets/content_join", array(
                'modules' => $event->getModules()
            ))
        );

        return true;
    }

}
