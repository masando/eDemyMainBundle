<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use eDemy\MainBundle\Event\ContentEvent;
//use FOS\UserBundle\Controller\SecurityController as BaseController;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\EventDispatcher\GenericEvent;
use eDemy\MainBundle\Entity\Param;

class LoginController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'login_lastmodified'    => array('onLoginLastmodified', 0),
            'login'                 => array('onLogin', 0),
            'register'              => array('onRegister', 0),
            'edemy_mainmenu'        => array('onLoginMainMenu', -100),
        ));
    }

    public function onLoginMainMenu(GenericEvent $menuEvent) {
        $items = array();
        $item = new Param($this->get('doctrine.orm.entity_manager'));
        if($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
//            die('a');
            $item->setName('Logout');
            $item->setValue('logout');
        } else {
            $item->setName('Login');
            $item->setValue('login');
        }
        $items[] = $item;

        $menuEvent['items'] = array_merge($menuEvent['items'], $items);

        return true;
    }

    public function onLoginLastmodified(ContentEvent $event)
    {
        //$lastmodified = $this->getLastModifiedFiles('/../../SecurityBundle/Resources/views', '*.html.twig');

        //$event->setLastModified($lastmodified);
//        $this->container = $this->get('service_container');
//        if (!$this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $lastmodified = new \DateTime();
            $event->setLastModified($lastmodified);
//        }
        //$this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'No tienes permisos para acceder a este recurso!');

        return true;
    }

    public function onLogin(ContentEvent $event)
    {
        $session = $this->get('session');
        $request = $this->get('request_stack')->getCurrentRequest();
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }
        $this->addEventModule($event, 'templates/login', array(
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
        ));

        return true;
    }

    public function getLogin($_target_path, $text = null)
    {
        //die(var_dump($

        return $this->render('login.html.twig', array(
            '_target_path' => $_target_path,
        ));

    }

    public function getRegister($_target_path, $text = null)
    {
        //die(var_dump($

        return $this->render('register.html.twig', array(
            '_target_path' => $_target_path,
        ));

    }

    public function onRegister(ContentEvent $event)
    {
        $this->addEventModule($event, 'register.html.twig', array(
            //'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            //'error'         => $error,
        ));

        return true;
    }

    protected function isGranted($attributes, $object = NULL)
    {
        if (false === $this->get('security.context')->isGranted($attributes)) {
            throw $this->newAccessDeniedException();
        }
    }
}
