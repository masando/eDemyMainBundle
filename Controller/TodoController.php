<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Component\EventDispatcher\GenericEvent;
use eDemy\MainBundle\Twig\TruncateHtmlString;
use eDemy\MainBundle\Event\ContentEvent;
use eDemy\MainBundle\Entity\Param;

class TodoController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', ['todo','tag'], array(
            'edemy_mainmenu'    => array('onTodoMainMenu', 0),
        ));
    }

    public function onTodoMainMenu(GenericEvent $menuEvent) {
        $items = array();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $item = new Param($this->get('doctrine.orm.entity_manager'));
            $item->setName('Admin_Todo');
            if($namespace = $this->getNamespace()) {
                $namespace .= ".";
            }
            $item->setValue($namespace . 'edemy_main_todo_index');
            $items[] = $item;
        }

        $menuEvent['items'] = array_merge($menuEvent['items'], $items);

        return true;
    }
}
