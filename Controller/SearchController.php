<?php

namespace eDemy\MainBundle\Controller;

use eDemy\MainBundle\Controller\BaseController;
use eDemy\MainBundle\Event\ContentEvent;
use Symfony\Component\EventDispatcher\GenericEvent;

class SearchController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('search', [], array(
            'edemy_precontent_module' => array('onPreContentModule', 0),
            'edemy_search_query' => array('onSearchQuery', 0),
        ));
    }

    public function onPreContentModule(ContentEvent $event) {
        if($this->getParam('precontentmodule_search', $this->getBundleName()) == 1) {
            if($this->getNamespace()) {
                $route = $this->getNamespace() . '.edemy_search_query';
            } else {
                $route = 'edemy_search_query';
            }
            $form = $this->get('form.factory')->createBuilder()
                ->setAction($this->get('router')->generate($route))
                ->setMethod('GET')
                ->add('search', 'text', array('label' => false))
                ->add('submit', 'submit', array('label' => "Buscar"))
                ->getForm();
            $this->addEventModule($event, 'templates/search_form', array(
                'form' => $form->createView(),
            ));
        }

        return true;
    }

    public function onSearchQuery(ContentEvent $event) {
        $request = $this->getRequest();
        $query = $request->query->get('form');
        $search = $query['search'];

        $searchEvent = new GenericEvent("search", array(
            'results' => array(),
            'search' => $search,
            'namespace' => $this->getNamespace(),
        ));

        $this->get('event_dispatcher')->dispatch('edemy_search_subquery', $searchEvent);

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $searchEvent['results'],
            $this->get('request')->query->get('page', 1)/*page number*/,
            10/*limit per page*/
        );


//        if(count($searchEvent['results'])) {
            $this->addEventModule($event, 'templates/search_result', array(
                //'results' => $searchEvent['results'],
                'pagination' => $pagination
            ));
//        }

        return true;        
    }
}
