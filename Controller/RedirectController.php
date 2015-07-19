<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use eDemy\MainBundle\Event\ContentEvent;
use eDemy\MainBundle\Entity\Param;
use Symfony\Component\EventDispatcher\GenericEvent;
use eDemy\MainBundle\Entity\Notfound;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Security\Core\Exception\AuthenticationCredentialsNotFoundException;
use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken;

class RedirectController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', ['notfound'], array(
            'edemy_mainmenu'                => array('onNotFoundMainMenu', 0),
        ));
    }

    public function onNotFoundMainMenu(GenericEvent $menuEvent) {
        $items = array();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $item = new Param($this->get('doctrine.orm.entity_manager'));
            $item->setName('Admin_NotFound');
            if($namespace = $this->getNamespace()) {
                $namespace .= ".";
            }
            $item->setValue($namespace . 'edemy_main_notfound_index');
            $items[] = $item;
        }

        $menuEvent['items'] = array_merge($menuEvent['items'], $items);

        return true;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $redirect = null;
        $exception = $event->getException();
        if ($exception instanceof HttpExceptionInterface) {
            if($exception->getStatusCode() == '404') {
                // SAVE URI IN DATABASE
                $entity = $this->saveUrl();
                // IF ENTITY HAS REDIRECT FOLLOW
                $options = (array) json_decode($entity->getOptions());
                $options = array_merge($options, array('_locale' => 'es'));
                try {
                    $redirect = $this->get('router')->generate($entity->getRedirect(), $options);
                    $response = new RedirectResponse($redirect, 302);
                    $event->setResponse($response);
                    $event->stopPropagation();
                } catch (RouteNotFoundException $e) {
                    // IF REDIRECT IS NO ROUTE
                    if(($rule = $this->getParam('redirect.rule')) !== 'redirect.rule') {
                        $url = $this->getRequest()->getRequestUri();
                        if($url[0] == '/') {
                            $url = substr($url, 1);
                        }
                        $url = str_replace('{url}', $url, $rule);
                    }

                    $response = new RedirectResponse($url, 302);
                    $event->setResponse($response);
                    $event->stopPropagation();

                    return true;
                }
                // IF NOT REDIRECT GENERATE 404
                $contentEvent = new ContentEvent('edemy_notfound');
                $contentEvent->setFormat('html');
                $this->addEventModule($contentEvent, 'templates/main/redirect/notfound');
                if($content = $this->getContent($contentEvent->getRoute())) {
                    $contentEvent->setContent($content);
                }
                $token = new AnonymousToken('main', 'anon.');
                $this->get('security.token_storage')->setToken($token);
                $this->get('request')->getSession()->invalidate();
                try {
                    if ($response = $this->getFullResponse($contentEvent)) {

                        $event->setResponse($response);
                    }
                } catch(AuthenticationCredentialsNotFoundException $ae) {
                    die(var_dump($contentEvent));
                }
            }
            //$response->setStatusCode($exception->getStatusCode());
            //$response->headers->replace($exception->getHeaders());
        }
    }
    
    public function saveUrl()
    {
        //SAVE 404 URL
        $em = $this->get('doctrine.orm.entity_manager');
        $currentUrl = $this->getRequest()->getUri();
        $entity = $em->getRepository('eDemyMainBundle:Notfound')->findOneByUrl($currentUrl);
        if(!$entity) {
            $entity = new Notfound($em);
            $entity->setUrl($currentUrl);
            $em->persist($entity);
            $em->flush();
        }

        return $entity;
    }
}
