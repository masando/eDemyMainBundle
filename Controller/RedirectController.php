<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use eDemy\MainBundle\Controller\BaseController;
use eDemy\MainBundle\Event\ContentEvent;
use eDemy\RedirectBundle\Entity\Notfound;

class RedirectController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', ['notfound']);
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof HttpExceptionInterface) {
            if($exception->getStatusCode() == '404') {
                $entity = $this->saveUrl();
                //die(var_dump((array) json_decode($entity->getOptions())));
                if($entity) {
                    $options = (array) json_decode($entity->getOptions());
                    $options = array_merge($options, array('_locale' => 'es'));
                    //die(var_dump($this->get('router')->generate($entity->getRedirect(), $options)));
                    $response = new RedirectResponse($this->get('router')->generate($entity->getRedirect(), $options), 302);
                } else {
                    $response = new RedirectResponse($this->get('router')->generate('edemy_main_frontpage'), 302);
                }
                //die(var_dump($response));
                $event->setResponse($response);
                $event->stopPropagation();
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
        $entity = $em->getRepository('eDemyRedirectBundle:Notfound')->findOneByUrl($currentUrl);
        if(!$entity) {
            $notfound = new Notfound($em);
            $notfound->setUrl($currentUrl);
            $em->persist($notfound);
            $em->flush();
            
            return false;
        }
        return $entity;
    }
}