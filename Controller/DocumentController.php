<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Component\EventDispatcher\GenericEvent;
use eDemy\MainBundle\Twig\TruncateHtmlString;
use eDemy\MainBundle\Event\ContentEvent;
use eDemy\MainBundle\Entity\Param;

class DocumentController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', ['document'], array(
            'edemy_main_document_frontpage_lastmodified' => array('onDocumentFrontpageLastModified', 0),
            'edemy_main_document_details_lastmodified'   => array('onDocumentDetailsLastModified', 0),
            'edemy_main_document_details'                => array('onDocumentDetails', 0),
            'edemy_main_document_page_details'           => array('onDocumentPageDetails', 0),
            'edemy_mainmenu'                             => array('onDocumentMainMenu', 0),
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
        $this->get('edemy.meta')->setTitlePrefix("Blog");
        $query = $this->getRepository($event->getRoute())->findAllOrderedByTitle($this->getNamespace(), true);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $this->get('request')->query->get('page', 1)/*page number*/,
            24/*limit per page*/
        );

        $this->addEventModule($event, 'templates/main/document/document', array(
            'pagination' => $pagination,
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
        $in = false;
        // @TODO si ha accedido mostrar contenido completo
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

        if($namespace = $this->getNamespace()) {
            $route = $namespace . '.edemy_main_document_details';
        } else {
            $route = 'edemy_main_document_details';
        }
        $redirectUrl = $this->get('router')->generate($route, array(
            'slug' => $entity->getSlug(),
        ), true);

        $login = $this->get('edemy.facebook')->fbLoginOrUser($redirectUrl);

        $this->get('edemy.meta')->setTitlePrefix($entity->getTitle());
        $description = new TruncateHtmlString($entity->getContent(), 300);
        if($this->isDevelopment()) {
//            die(var_dump());
        }
        $this->get('edemy.meta')->addMeta(array(
            'og:url' => $request->getUri(),
            'og:site_name' => 'http://www.maste.es',
            'og:title' => $entity->getTitle(),
            'og:description' => strip_tags($description->cut()),
            'og:type' => 'article',
            'og:locale' => 'es_ES',
            'fb:app_id' => $this->getParam('app.id', 'eDemyFbBundle'),
            // @TODO author param
            'article:author' => "https://www.facebook.com/tecafeycomplementos",
            // @TODO publisher param
            'article:publisher' => "https://www.facebook.com/tecafeycomplementos",
        ));
        if(count($entity->getImagenes())) {
            $this->get('edemy.meta')->addMeta(
                array(
                    'og:image' => 'http://'.$this->getRequest()->getHost().$entity->getImagenes()->first()->getWebpath(
                            300
                        ),
                )
            );
        }

/*
    <meta property="og:url" content="{{ 'http://' ~ app.request.host ~ path(app.request.attributes.get('_route'), app.request.attributes.get('_route_params')) }}" />
    <meta property="og:site_name" content="{{ app.request.host }}"/>
    <meta property="og:title" content="{{ entity.title }}" />
    <meta property="og:description" content="{{ entity.content|truncatehtml(500)|raw }}" />
    <meta property="og:type" content="article" />
    <meta property="og:locale" content="es_ES" />
    {% if appId is defined %}
        <meta property="fb:app_id" content="{{ appId }}" />
    {% endif %}
    <meta property="article:author" content="https://www.facebook.com/tecafeycomplementos" />
    <meta property="article:publisher" content="https://www.facebook.com/tecafeycomplementos" />
    {% if entity.imagenes|length > 0 %}
        <meta property="og:image" content="{{ entity.imagenes.first.webpath(600, null, app.request.host) }}" />
    {% endif %}
*/

        $this->addEventModule($event, 'templates/main/document/document_details', array(
            'entity' => $entity,
            'login' => $login,
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
