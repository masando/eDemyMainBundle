<?php

namespace eDemy\MainBundle\Controller;

//use eDemy\MainBundle\Controller\BaseController;
use eDemy\MainBundle\Entity\Param;
use Symfony\Component\EventDispatcher\GenericEvent;
use eDemy\MainBundle\Event\ContentEvent;

use eDemy\MainBundle\Form\ContactType;

class ContactController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_contact_frontpage_lastmodified' => array('onFrontpageLastModified', 0),
            'edemy_contact_frontpage' => array('onContactFrontpage', 0),
            'edemy_mainmenu' => array('onContactMainMenu', 0),
        ));
    }

    public function onContactMainMenu(GenericEvent $menuEvent) {
        //      $menu = 'mainmenu';
        //        $request = $this->get('request_stack')->getCurrentRequest();
        //        $_route = $request->attributes->get('_route');
        //        $namespace = $this->getNamespace('edemy_contact_frontpage');
        //        $items = $this->getParamByType($menu, $namespace, $this->getBundleName());

        $items = array();
        $item = new Param($this->get('doctrine.orm.entity_manager'));
//                $item->setBundle($this->getBundleName());
//                $item->setType('mainmenu');
        $item->setName('Contacto');
        if($namespace = $this->getNamespace()) {
            $namespace .= ".";
        }
        $item->setValue($namespace . 'edemy_contact_frontpage');
        $items[] = $item;

        $menuEvent['items'] = array_merge($menuEvent['items'], $items);
//die(var_dump($menuEvent['items']));
        return true;
    }

    public function onFrontpageLastModified(ContentEvent $event)
    {
        $event->setLastModified(new \DateTime());

        return true;
    }
    
    public function onContactFrontpage(ContentEvent $event)
    {
        $this->get('edemy.meta')->setTitlePrefix("Formulario de Contacto");
        $request = $this->getCurrentRequest();
        $host = $frontcontroller = $request->getHost();
        $env = $this->get('kernel')->getEnvironment();
        $mailmsg = 'Mensaje desde la web';
        $mailto = $this->getParam('sendtomail');
        //$mailfrom = $this->getParam('sendfrommail');
        $namespace = $this->getNamespace();
        if($namespace) {
            $action = $this->get('router')->generate($namespace . '.' . 'edemy_contact_frontpage');
        } else {
            $action = $this->get('router')->generate('edemy_contact_frontpage');
        }
        $form = $this->get('form.factory')->create(
            new ContactType(), 
            null, 
            array(
                'action' => $action,
                'method' => 'POST',
            )
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();

            $message = \Swift_Message::newInstance()
                ->setSubject($mailmsg)
                ->setFrom($data['email'])
                ->setTo($mailto)
                ->setBcc('manuel@edemy.es')
                ->setBody(
                    "Nombre: ".$data['nombre'].
                    "\nEmail: ".$data['email'].
                    "\nMensaje: ".$data['mensaje']
                )
            ;
            $this->get('mailer')->send($message);

            $this->get('session')->getFlashBag()->add(
                'notice',
                'Tu mensaje ha sido enviado. Gracias.'
            );

            $event->setContent($this->newRedirectResponse('edemy_contact_frontpage'));
            $event->stopPropagation();

            return true;
        }

        $this->addEventModule($event, 'templates/main/contact/contact', array(
            'form' => $form->createView(),
        ));
        
        return true;
    }
}
