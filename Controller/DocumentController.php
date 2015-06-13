<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Component\EventDispatcher\GenericEvent;
use eDemy\MainBundle\Event\ContentEvent;
use eDemy\MainBundle\Entity\Param;

class DocumentController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', ['document'], array(
            'edemy_document_frontpage_lastmodified' => array('onDocumentFrontpageLastModified', 0),
            'edemy_document_details_lastmodified'   => array('onDocumentDetailsLastModified', 0),
            'edemy_document_details'                => array('onDocumentDetails', 0),
            'edemy_document_page_details'           => array('onDocumentPageDetails', 0),
            'edemy_mainmenu'                        => array('onDocumentMainMenu', 0),
        ));
    }

    public function onDocumentMainMenu(GenericEvent $menuEvent) {
        $items = array();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $item = new Param($this->get('doctrine.orm.entity_manager'));
            $item->setName('Admin_Document');
            if($namespace = $this->getNamespace()) {
                $namespace .= ".";
            }
            $item->setValue($namespace . 'edemy_main_document_index');
            $items[] = $item;
        }

        $menuEvent['items'] = array_merge($menuEvent['items'], $items);

        return true;
    }

    public function onDocumentFrontpageLastModified(ContentEvent $event)
    {
        $document = $this->getRepository('edemy_main_document_frontpage')->findLastModified($this->getNamespace());
        if($document->getUpdated()) {
            $event->setLastModified($document->getUpdated());
        }

        return true;
    }

    public function onDocumentFrontpage(ContentEvent $event)
    {
        $entities = $this->getRepository()->findAllOrderedByName();

        $this->addEventModule($event, 'templates/document', array(
            'domain' => 'document',
            'entities' => $entities,
        ));

        return true;
    }

    public function onDocumentDetailsLastModified(ContentEvent $event)
    {
        $entity = $this->getRepository($this->getBundleName().':' . $this->getEntityNameUpper())->findOneBy(array(
            'slug'        => $this->getRequestParam('slug'),
            //'namespace' => $this->getNamespace(),
        ));
        if($entity) {
            //die(var_dump($entity->getUpdated()));
            $event->setLastModified($entity->getUpdated());
        }
    }

    public function onDocumentDetails(ContentEvent $event)
    {
        $request = $this->getRequest();
        $slug = $request->attributes->get('slug');
//        die();
        $entity = $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName() . ':' . $this->getEntityNameUpper())->findOneBy(array(
            'slug' => $slug,
            //'namespace' => $this->getNamespace(),
        ));
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Document entity.');
        }

        $this->addEventModule($event, 'templates/document_details', array(
            'entity' => $entity,
        ));

        return true;
    }

    public function getDocument($name)
    {
        $entity = $this->get('doctrine.orm.entity_manager')->getRepository('eDemyDocumentBundle:Document')->findOneBy(array(
            'name' => $name,
            //'namespace' => $this->getNamespace(),
        ));
        if($entity) {
            return $entity;
        } else {
            return null;
        }
    }

    public function onDocumentPageDetails(ContentEvent $event)
    {
        $request = $this->getRequest();
        $bundle = $request->attributes->get('bundle');
        $file = $request->attributes->get('file');

        $carrusel = $this->get('doctrine.orm.entity_manager')->getRepository('eDemyCarruselBundle:Carrusel')->find(2);
        $document = $this->getDocument("quienes somos");
        $page = $this->get('templating')->render('eDemy' . ucfirst($bundle) . 'Bundle::pages/' . $file . '.html.twig', array(
            'carrusel' => $carrusel,
            'document' => $document->getContent(),
        ));

        $event->addModule($page);
        //die();

        return true;
    }
}
