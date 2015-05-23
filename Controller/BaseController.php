<?php

namespace eDemy\MainBundle\Controller;

use Doctrine\Bundle\DoctrineBundle\Mapping\DisconnectedMetadataFactory;
use eDemy\MainBundle\Event\ContentEvent;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\eventDispatcher\eventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\EventDispatcher\GenericEvent;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
//use Symfony\Component\Stopwatch\Stopwatch;

use eDemy\MainBundle\Entity\Param;
use eDemy\MainBundle\Entity\BaseEntity;

abstract class BaseController extends Controller implements EventSubscriberInterface
{
    /** @var eventDispatcherInterface $eventDispatcher */
    protected $eventDispatcher;
    protected $class;
    protected $environment;
    protected $stopwatch;

    public static function getEntitySubscriptions($bundle, $entity_name)
    {
        $main_subscriptions = array(
            'edemy_'.$bundle.'_'.$entity_name.'_frontpage' => array('on'.ucfirst($entity_name).'Frontpage', 0),
            'edemy_'.$bundle.'_'.$entity_name.'_index' => array('onIndex', 0),
            'edemy_'.$bundle.'_'.$entity_name.'_edit' => array('onEdit', 0),
            'edemy_'.$bundle.'_'.$entity_name.'_new' => array('onNew', 0),
            'edemy_'.$bundle.'_'.$entity_name.'_show' => array('onShow', 0),
            'edemy_'.$bundle.'_'.$entity_name.'_create' => array('onCreate', 0),
            'edemy_'.$bundle.'_'.$entity_name.'_update' => array('onUpdate', 0),
            'edemy_'.$bundle.'_'.$entity_name.'_delete' => array('onDelete', 0),
        );

        return $main_subscriptions;
    }

    public static function getSubscriptions($bundle, $entities = array(), $route_subscriptions = array())
    {
        $subscriptions = array();
        $subscriptions = $subscriptions + array(
                'edemy_main_mainmenu' => array('onMainMenu', 0),
                'edemy_main_adminmenu' => array('onAdminMenu', 0),
                'edemy_main_footermenu' => array('onFooterMenu', 0),
                //'edemy_css_lastmodified'    => array('onCssLastModified', 0),
                'edemy_css_module' => array('onCssModule', 0),
                'edemy_javascript_module' => array('onJavascriptModule', 0),
                'edemy_sitemap_module' => array('onSitemapModule', 0),
                'edemy_'.$bundle.'_frontpage' => array('onFrontpage', 0),
                'edemy_search_subquery' => array('onSearchSubQuery', 0),
            );
        foreach ($entities as $entity) {
            $subscriptions = $subscriptions + self::getEntitySubscriptions($bundle, $entity);
        }
        $subscriptions = $subscriptions + $route_subscriptions;

        return $subscriptions;
    }

    public static function getSubscribedEvents() {}

    public function setEventDispatcher(eventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->environment = $this->get('kernel')->getEnvironment();
        if ($this->isDevelopment()) {
            $this->stopwatch = $this->get('debug.stopwatch');
        }
    }

    public function __construct()
    {
        $this->class = get_class($this);
    }

    public function dispatch($event_name, $event)
    {
        if ($this->eventDispatcher) {
            $this->get('event_dispatcher')->dispatch($event_name, $event);

            return true;
        }

        return false;
    }

    // ROUTING TOOLS
    public function getNamespaceFromRoute() {
        $parts = explode('.', $_route);
        if (count($parts) == 2) {
            return $parts[0];
        }

        return null;
    }

    public function getRouteWithoutNamespace() {
        $_route = $this->getRoute();
        $parts = explode('.', $_route);
        if (count($parts) == 2) {
            return end($parts);
        }

        return $_route;
    }

    public function getRoute()
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $_route = $request->attributes->get('_route');

        return $_route;
    }

    public function getNamespace($_route = null)
    {
        if ($_route == null) {
            $request = $this->get('request_stack')->getCurrentRequest();
            $_route = $request->attributes->get('_route');
        }
        $parts = explode('.', $_route);
        if (count($parts) == 2) {
            return $parts[0];
        }

        return null;
    }

    //// TEMPLATING TOOLS
    public function render($template, array $options = array(), Response $response = null)
    {
        if (strpos($template, 'dmin/')) {
            return $this->get('templating')->render('eDemyMainBundle::'.$template, $options);
        } else {
            // @TODO add themeBundle param
            //die();
//            return parent::render($this->getBundleName().'::' . $template, $options);
            return $this->get('templating')->render($this->getTemplate($template), $options);
        }
    }

    public function getTemplate($template, $_format = 'html') {
        $template = $this->getParam("themeBundle", null, $this->getBundleName()) .
            '::' .
            $this->getParam($template, null, "layout/theme").'.'.$_format.'.twig';
        //die(var_dump($template));
        return $template;
    }

    // PARAM TOOLS
    public function getParamByType($type, $namespace = null, $bundle = null)
    {
        $event = new GenericEvent(
            "param_type", array(
            'type' => $type,
            'value' => '',
            'namespace' => $namespace,
            'bundle' => $bundle,
        )
        );
        $this->eventDispatcher->dispatch('edemy_param_by_type', $event);
        if ($type == 'prefix') {
        }

        return $event['values'];
    }

    public function getParamBy($options = null)
    {
        if ($options) {
            $event = new GenericEvent("param_by", $options);
            $this->eventDispatcher->dispatch('edemy_param_by', $event);

            return $event['values'];
        }

        return null;
    }

    public function getParam($param, $bundle = null, $default = null, $namespace = null, $object = false)
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $_route = $request->attributes->get('_route');

        if ($namespace == null) {
            $namespace = $this->getNamespace($_route);
        }
        //$name_parts = explode('.', $param);
        if ($bundle == null) {
            $bundle = $this->getBundleName();
        }
        $event = new GenericEvent(
            "param",
            array(
                'param' => $param,
                'bundle' => $bundle,
                //'default' => end($name_parts),
                'default' => $default,
                'value' => '',
                'namespace' => $namespace,
                'object' => $object,
            )
        );
        $this->eventDispatcher->dispatch('edemy_param', $event);
        //return $event['value'];
        if ($event['value'] == '') {
            return $default;
        } else {
            return $event['value'];
        }
    }

    // REQUEST TOOLS
    public function getRequest()
    {
        $request = $this->get('request_stack')->getCurrentRequest();

        return $request;
    }

    public function getMasterRequest()
    {
        $request = $this->get('request_stack')->getMasterRequest();

        return $request;
    }

    public function getRequestParam($param)
    {
        $request = $this->getRequest();

        return $request->attributes->get($param);
    }

    public function getCurrentRequest()
    {
        return $this->get('request_stack')->getCurrentRequest();
    }

    // RESPONSE
    public function newResponse($content = null)
    {
        return new Response($content);
    }

    public function newRedirectResponse($route, $options = array())
    {
        if ($this->getNamespace()) {
            $redirect = $this->get('router')->generate($this->getNamespace().'.'.$route, $options);
        } else {
            $redirect = $this->get('router')->generate($route, $options);
        }

        return new RedirectResponse($redirect);
    }

    // CONTAINER TOOLS
    public function get($service)
    {
        $event = new GenericEvent(
            "service",
            array('name' => $service)
        );
        $this->eventDispatcher->dispatch('edemy_service', $event);

        return $event['service'];
    }

    ////

    // EVENTS
    public function addEventModule(ContentEvent $event, $template, $params = array())
    {
        if ($template) {
            $content = $this->render($template, $params);
            if (strlen($content) > 5) {
                $event->addModule($content);
            }
        } else {
            $event->addModule($params);
        }

        return $event;
    }

    public function addEventContent(ContentEvent $event, $template, $params = array())
    {
        $event->setContent(
            $this->newResponse(
                $this->render($template, $params)
            )
        );
        $event->stopPropagation();

        return $event;
    }

    // EXCEPTIONS
    public function newAccessDeniedException()
    {
        return new AccessDeniedException();
    }

    public function createNotFoundException($message = 'Not Found', \Exception $previous = null)
    {
        return new NotFoundHttpException($message, $previous);
    }

    //
    public function findAll($entity = null)
    {
        if($entity == 'Background') {
            //die(var_dump($this->getBundleName() . ':' . $entity));
        }
        if($entity == null) $entity = $this->getEntityNameUpper();
        
        return $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName() . ':' . $entity)->findBy(array(
            'namespace' => $this->getNamespace(),
        ));
    }

    // DOCTRINE
    public function getEm()
    {
        return $this->get('doctrine.orm.entity_manager');
    }

    public function getRepository($_route = null)
    {
        return $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName().':'.$this->getEntityNameUpper($_route));
    }

    public function getControllerName()
    {
        $parts = explode('\\', $this->class);
        $name = strtolower(str_replace("Controller", "", end($parts)));
        
        return $name;
    }

    /*
    public function onCssLastModified(ContentEvent $event)
    {
        $reflection = new \ReflectionClass(get_class($this));
        $dir = dirname($reflection->getFileName());
        
        if(get_class($this) != "eDemy\MainBundle\Controller\MainController") {
            //die(var_dump(get_class($this)));
            //die(var_dump($dir));
        }
        
        
        $finder = new Finder();
        $finder
            ->files()
            ->in($dir . '/../Resources/views/css')
            ->sortByModifiedTime();
        foreach ($finder as $file) {
            //print $file->getRealpath()."-";
            //print date($file->getMTime())."<br/>";
        }
        //print date($file->getMTime())."<br/>";
        $lastmodified = new \DateTime();
        $lastmodified = \DateTime::createFromFormat( 'U', $file->getMTime() );
        //$formattedString = $currentTime->format( 'c' );
        //die(var_dump($currentTime));
        //obtener la última fecha de modificación
        //$lastmodified = ;
        if($event->getLastModified() > $lastmodified) {
            //$event->setLastModified($lastmodified);
        } else {
            $event->setLastModified($lastmodified);
        }
        //die(var_dump($event->getLastModified()));
    }
    */

    // EVENT LISTENERS
    public function onFrontpage(ContentEvent $event)
    {
        return true;
    }

    public function onMainMenu($menuEvent)
    {
        $menu = 'mainmenu';
        $request = $this->get('request_stack')->getCurrentRequest();
        $_route = $request->attributes->get('_route');
        $namespace = $this->getNamespace($_route);
        $items = $this->getParamByType($menu, $namespace, $this->getBundleName());

        if($items) {
            $menuEvent['items'] = array_merge($menuEvent['items'], $items);
            //$menuEvent['items'] = $menuEvent['items'] + $items;
        }

        return true;
    }

    public function onAdminMenu($menuEvent)
    {
        //$request = $this->get('request_stack')->getCurrentRequest();
        //$_route = $request->attributes->get('_route');
        //$namespace = $this->getNamespace($_route);
        $menu = 'adminmenu';
        $namespace = $menuEvent['namespace'];
        $items = $this->getParamByType($menu, $namespace, $this->getBundleName());

        if($items) {
            $menuEvent['items'] = array_merge($menuEvent['items'], $items);
            /*
            foreach($items as $item) {
                if($item->getBundle() == $this->getBundleName()) {
                    //$menuEvent['items'][] = $item;
                }
            }
            * */
        }

        return true;
    }

    public function onFooterMenu($menuEvent)
    {
        $menu = 'footermenu';
        $request = $this->get('request_stack')->getCurrentRequest();
        $_route = $request->attributes->get('_route');
        $namespace = $this->getNamespace($_route);
        $items = $this->getParamByType($menu, $namespace, $this->getBundleName());

        if($items) {
            $menuEvent['items'] = array_merge($menuEvent['items'], $items);
            //$menuEvent['items'] = $menuEvent['items'] + $items;
        }

        return true;
    }

    public function onCssModule(ContentEvent $event)
    {
        /** @var Param[] $allparams */
        $allparams = $this->getParamByType('css');
        $params = array();
        //$allparams = $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName().':Param')->findAll();
        if($allparams) {
            foreach($allparams as $param) {
                $params[$param->getName()] = $param->getValue();
            }
        }
        $this->addEventModule($event, "css/" . $this->getControllerName() . ".css.twig", array('params' => $params));

        return true;
    }

    public function onSitemapModule(ContentEvent $event) {
        /** @var Param[] $prefixes */
        $prefixes = $this->getParamByType('prefix');
        //$bundlename = $this->getBundleName(true);
        $entityNames = $this->getBundleEntities();
        $urls = array();

        //bundle frontpage route without namespace
        if($this->getParam('sitemap_bundle') == '1') {
            $urls[] = $this->get('router')->generate('edemy_' . strtolower($this->getBundleName(false)) . '_frontpage', array(), true);
        }

        //bundle frontpage route with namespace
        foreach($prefixes as $prefix) {
            if($this->getParam('sitemap_bundle', null, null, $prefix->getValue()) == '1') {
                $urls[] = $this->get('router')->generate($prefix . '.' . 'edemy_' . strtolower($this->getBundleName(false)) . '_frontpage', array(), true);
            }
        }

        //bundle manual route without namespace
        if($this->getParam('sitemap_route') == '1') {
            $route = $this->getParam('sitemap_route', null, null, null, true);
            if($route->getDescription() != null) {
                $options = json_decode($route->getDescription(), false);
                $urls[] = $this->get('router')->generate($options->route, array(), true);
            }
        }

        //bundle manual route with namespace
        foreach($prefixes as $prefix) {
            if($this->getParam('sitemap_route', null, null, $prefix->getValue()) == '1') {
                $route = $this->getParam('sitemap_route', null, null, $prefix->getValue(), true);
                if($route->getDescription() != null) {
                    $options = json_decode($route->getDescription(), false);
                    $urls[] = $this->get('router')->generate($prefix . '.' . $options->route, array(), true);
                }
            }
        }

        //entity routes without namespace
        foreach($entityNames as $entityName) {
            if($entityName != 'Param') {
                if($this->getParam('sitemap_entity') == '1') {
                    $param = $this->getParam('sitemap_entity', null, null, null, true);
                    if($param->getDescription() != null) {
                        $options = json_decode($param->getDescription(), false);
                        $entity = $options->entity;
                        if($entity == $entityName) {
                            //entity frontpage route without namespace
                            $urls[] = $this->get('router')->generate('edemy_' . strtolower($this->getBundleName(false)) . '_' . strtolower($entityName) . '_frontpage', array(), true);
                            $em = $this->get('doctrine.orm.entity_manager');
                            /** @var BaseEntity[] $entities */
                            $entities = $em->getRepository($this->getBundleName() . ':' . $entity)->findBy(array(
                                'published' => true,
                                'namespace' => '',
                            ));
                            foreach($entities as $entity) {
                                if($entity->getSlug()) {
                                    //entity details route without namespace
                                    $urls[] = $this->get('router')->generate('edemy_' . strtolower($this->getBundleName(false)) . '_' . strtolower($entityName) . '_details', array( 'slug' => $entity->getSlug()), true);
                                }
                            }
                        }
                    }

                }
            }
        }

        //entity routes with namespace
        foreach($prefixes as $prefix) {
            foreach($entityNames as $entityName) {
                if($entityName != 'Param') {
                    if($this->getParam('sitemap_entity', null, null, $prefix->getValue()) == '1') {
                        $param = $this->getParam('sitemap_entity', null, null, $prefix->getValue(), true);
                        if($param->getDescription() != null) {
                            $options = json_decode($param->getDescription(), false);
                            $entity = $options->entity;
                            if($entity == $entityName) {
                                //entity frontpage route with namespace
                                $urls[] = $this->get('router')->generate($prefix . '.' . 'edemy_' . strtolower($this->getBundleName(false)) . '_' . strtolower($entityName) . '_frontpage', array(), true);
                                $entities = $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName() . ':' . $entity)->findBy(array(
                                    'published' => true,
                                    'namespace' => $prefix->getValue(),
                                ));
                                foreach($entities as $entity) {
                                    if($entity->getSlug()) {
                                        //entity details route with namespace
                                        $urls[] = $this->get('router')->generate($prefix . '.' . 'edemy_' . strtolower($this->getBundleName(false)) . '_' . strtolower($entityName) . '_details', array( 'slug' => $entity->getSlug()), true);
                                    }
                                }
                            }
                        }

                    }
                }
            }
        }
        if($this->getBundleName(false) == 'Event') {
            //die(var_dump($urls));
        }
        $this->addEventModule($event, null, $urls);

        return true;
    }

    public function onJavascriptModule(ContentEvent $event)
    {
        /** @var Param[] $allparams */
        $allparams = $this->getParamByType('javascript');
        $params = array();
        //$allparams = $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName().':Param')->findAll();
        if($allparams) {
            foreach($allparams as $param) {
                $params[$param->getName()] = $param->getValue();
            }
        }
        $this->addEventModule($event, "js/" . $this->getControllerName() . ".js.twig", array('params' => $params));

        return true;
    }

    //// onIndex
    public function onIndex(ContentEvent $event)
    {
        $repository = $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName().':'.$this->getEntityNameUpper());
        //die(var_dump($this->getNamespace()));
        $entities = array_merge($this->findAll($this->getEntityNameUpper()) , $repository->findByNamespace('all'));
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $entities,
            $this->get('request')->query->get('page', 1),
            100
        );
        $entities = $pagination->getItems();
        foreach($entities as $entity) {
            $entity->setEntityManager($this->get('doctrine.orm.entity_manager'));
            $entity->setMappings();
        }

        $this->addEventModule($event, "admin/index.html.twig", array(
            'entities' => $pagination->getItems(),
            'pagination' => $pagination,
            'entity_name' => $this->getEntityName(),
            'entity_path' => $this->getEntityPath(),
            'edit_route' => 'edemy_' . $this->getEntityPath() . '_edit',
            'new_route' => 'edemy_' . $this->getEntityPath() . '_new',
        ));
    }

    //// onShow
    public function onShow(ContentEvent $event)
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $id = $request->attributes->get('id');
        $em = $this->get('doctrine.orm.entity_manager');
        $entity = $em->getRepository($this->getBundleName().':' . $this->getEntityNameUpper())->findOneBy(array(
            'id' => $id,
            //'namespace' => $this->getNamespace(),
        ));
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Param entity.');
        }
        $entity->setEntityManager($this->get('doctrine.orm.entity_manager'));
        $entity->setMappings();
        $deleteForm = $this->createDeleteForm($id);
        $this->addEventModule($event, 'admin/show.html.twig', array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'entity_name' => $this->getEntityName(),
            'entity_path' => $this->getEntityPath(),
            'edit_route' => 'edemy_' . $this->getEntityPath() . '_edit',
            'index_route' => 'edemy_' . $this->getEntityPath() . '_index',
        ));

        return true;
    }

    //// onNew
    public function onNew(ContentEvent $event)
    {
        $entity = $this->getNewEntity($this->get('doctrine.orm.entity_manager'));
        //$entity->setEntityManager($this->get('doctrine.orm.entity_manager'));
        //$entity->setMappings();
        $this->addEventModule($event, 'admin/new.html.twig', array(
            'entity' => $entity,
            'form'   => $this->createNewForm($entity)->createView(),
            'entity_name' => $this->getEntityName(),
            'entity_path' => $this->getEntityPath(),
            'bundle_name' => $this->getBundleName(false),
            'index_route' => 'edemy_' . $this->getEntityPath() . '_index',
        ));

        return true;
    }

    private function createNewForm($entity)
    {
        $formClass = "eDemy\\MainBundle\\Form\\BaseType";
        $formType = new $formClass($entity);
        if($this->getNamespace()) {
            $action = $this->get('router')->generate($this->getNamespace() . '.' . 'edemy_' . $this->getEntityPath() . '_create');
        } else {
            $action = $this->get('router')->generate('edemy_' . $this->getEntityPath() . '_create');
        }
        $form = $this->get('form.factory')->create($formType, $entity, array(
            'action' => $action,
            'method' => 'POST',
        ));
        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    public function onCreate(ContentEvent $event)
    {
        $entityClass = substr($this->getBundleName(), 0, 5) . '\\' . substr($this->getBundleName(), 5) .'\\Entity\\' . $this->getEntityNameUpper();
        $entity = new $entityClass($this->get('doctrine.orm.entity_manager'));
        //$entity->setMappings($this->getFieldMappings(), $this->getAssociationMappings());
        $request = $this->get('request_stack')->getCurrentRequest();
        $form = $this->createNewForm($entity);
        $form->handleRequest($request);
        if ($form->isValid()) {
            if($entity->getNamespace() == null) {
                $entity->setNamespace($this->getNamespace());
            }
            $this->get('doctrine.orm.entity_manager')->persist($entity);
            $this->get('doctrine.orm.entity_manager')->flush();

            $event->setContent($this->newRedirectResponse('edemy_' . $this->getEntityPath() . '_show', array('id' => $entity->getId())));
            $event->stopPropagation();

            return true;
        }
        $this->addEventModule($event, 'admin/new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));

        return true;
    }

    //// onEdit
    public function onEdit(ContentEvent $event)
    {
        $request = $this->get('request_stack')->getCurrentRequest();
        $id = $request->attributes->get('id');
        $entity = $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName().':'.$this->getEntityNameUpper())->findOneBy(array(
            'id' => $id,
            //'namespace' => $this->getNamespace(),
        ));
        if (!$entity) {
            throw $this->CreateNotFoundException('Unable to find entity.');
        }
        $entity->setEntityManager($this->get('doctrine.orm.entity_manager'));
        $entity->setMappings();
        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);
        $this->addEventModule($event, 'admin/edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'entity_name' => $this->getEntityName(),
            'entity_path' => $this->getEntityPath(),
            'index_route' => 'edemy_' . $this->getEntityPath() . '_index',
            'bundle_name' => $this->getBundleName(false),
        ));

        return true;
    }
    
    private function createEditForm($entity)
    {
        $entityClass = substr($this->getBundleName(), 0, 5) . '\\' . substr($this->getBundleName(), 5) .'\\Entity\\' . $this->getEntityNameUpper();
        $formClass = substr($this->getBundleName(), 0, 5) . '\\' . 'MainBundle' .'\\Form\\' . 'Base' . 'Type';
        $formType = new $formClass($entity);
        //$formType->setEntityName($entityClass);
        if($this->getNamespace()) {
            $action = $this->get('router')->generate($this->getNamespace() . '.' . 'edemy_' . $this->getEntityPath() . '_update', array('id' => $entity->getId()));
        } else {
            $action = $this->get('router')->generate('edemy_' . $this->getEntityPath() . '_update', array('id' => $entity->getId()));
        }
        //die(var_dump($action));
        $form = $this->get('form.factory')->create($formType, $entity, array(
            'action' => $action,
            'method' => 'PUT',
        ));
        $form->add('submit', 'submit', array('label' => $this->getParam($this->getEntityPath() . '.update')));

        return $form;
    }

    public function onUpdate(ContentEvent $event)
    {
        $this->em = $this->get('doctrine.orm.entity_manager');
        $request = $this->get('request_stack')->getCurrentRequest();
        $id = $request->attributes->get('id');        
        $entity = $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName().':'.$this->getEntityNameUpper())->find(array(
            'id' => $id,
            //'namespace' => $this->getNamespace(),
        ));
        if (!$entity) {
            throw $this->CreateNotFoundException('Unable to find Entity.');
        }
        $entity->setEntityManager($this->get('doctrine.orm.entity_manager'));
        $entity->setMappings();
        //$deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);
        if ($editForm->isValid()) {
            if(($entity->getNamespace() == null) and ($this->getNamespace() !== null)) {
                $entity->setNamespace($this->getNamespace());
            }
            $this->em->persist($entity);
            $this->em->flush();

            $event->setContent($this->newRedirectResponse('edemy_' . $this->getEntityPath() . '_index'));
            $event->stopPropagation();

            return true;
        }
        $this->addEventModule($event, 'admin/edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            //'delete_form' => $deleteForm->createView(),
        ));

        return true;
    }

    //// onDelete
    public function onDelete(ContentEvent $event)
    {
        $this->em = $this->get('doctrine.orm.entity_manager');
        $request = $this->get('request_stack')->getCurrentRequest();
        $id = $request->attributes->get('id');
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        if ($form->isValid()) {
            $entity = $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName().':'.$this->getEntityNameUpper())->findOneBy(array(
                'id' => $id,
                'namespace' => $this->getNamespace(),
            ));
            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Param entity.');
            }
            /*
            foreach($entity->getImagenes() as $image) {
                $this->em->remove($image);
            }
            foreach($entity->getPresupuestos() as $presupuesto) {
                $this->em->remove($presupuesto);
            }
            */
        //die(var_dump($entity));
            $this->em->remove($entity);
            $this->em->flush();
        }
        $event->setContent($this->newRedirectResponse('edemy_' . $this->getEntityPath() . '_index'));
        $event->stopPropagation();

        return true;
    }

    private function createDeleteForm($id)
    {
        if($this->getNamespace()) {
            $action = $this->get('router')->generate($this->getNamespace() . '.' . 'edemy_' . $this->getEntityPath() . '_delete', array('id' => $id));
        } else {
            $action = $this->get('router')->generate('edemy_' . $this->getEntityPath() . '_delete', array('id' => $id));
        }
        return $this->get('form.factory')->createBuilder()
            ->setAction($action)
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => $this->getParam('param.delete', null, 'delete')))
            ->getForm()
        ;
    }

    public function setEntityManager($entity)
    {
        if(method_exists($entity, "setEntityManager")) {
            $entity->setEntityManager($this->get('doctrine.orm.entity_manager'));
        }
    }
    
    //// BUNDLE AND ENTITIES FUNCTIONS
    public function getBundleName($full = true)
    {
        $bundleName = explode('\\', $this->class);
        if($full) {
            return $bundleName[0] . $bundleName[1];
        } else {
            $bundleNameSimple = explode('Bundle', $bundleName[1]);
            return $bundleNameSimple[0];
        }
    }

    /**
     *  return entity name as "entity"
     */
    public function getEntityName($_route = null)
    {
        if($_route == null) {
            $request = $this->get('request_stack')->getCurrentRequest();
            $_route = $request->attributes->get('_route');
        }
        $parts = explode('.', $_route);
        if(count($parts) == 2) {
            $namespace = $parts[0];
        }
        $_route = end($parts);
        $parts = explode('_', $_route);
        if(count($parts) > 1) {
            $entity = $parts[count($parts)-2];
        } else {
            $parts = explode(':', $_route);
            if(count($parts) == 2) {
                $entity = strtolower($parts[1]);
            }
        }
        //if((count($parts)-2) == -1) die(var_dump($_route));
        //die(var_dump(count($parts)));
        //die(var_dump($entity));
        return $entity;
    }

    /**
     *  return entity name as "Entity"
     */
    public function getEntityNameUpper($_route = null)
    {
        return ucfirst($this->getEntityName($_route));
    }
    
    /**
     *  return entity name as "entity" or "bundle_entity"
     */
    public function getEntityPath($_route = null)
    {
        if($_route == null) {
            $request = $this->get('request_stack')->getCurrentRequest();
            $_route = $request->attributes->get('_route');
        }
        $parts = explode('.', $_route);
        if(count($parts) == 2) {
            $namespace = $parts[0];
        }
        $_route = end($parts);
        $parts = explode('_', $_route);
        if(count($parts) == 3) {
            $entity = $parts[count($parts)-2];
        } else {
            $entity = $parts[count($parts)-3] . '_' . $parts[count($parts)-2];
        }

        return $entity;
    }

    /**
     *  return entity name as full name
     */
    public function getEntityClass()
    {
        $entityClass = substr($this->getBundleName(), 0, 5) . '\\' . substr($this->getBundleName(), 5) .'\\Entity\\' . $this->getEntityNameUpper();
        return $entityClass;
    }

    /**
     *  return entity name as object
     */
    public function getNewEntity($em = null)
    {
        $entityClass = $this->getEntityClass();
        return new $entityClass($em);
    }
        
    public function getBundleEntities($bundleName = null)
    {
        $manager = new DisconnectedMetadataFactory($this->get('doctrine'));
        if($bundleName == null) {
            $bundle = $this->get('kernel')->getBundle($this->getBundleName());
        } else {
            $bundle = $this->get('kernel')->getBundle($bundleName);
        }
        //die(var_dump($bundle));
        try {
            $metadata = $manager->getBundleMetadata($bundle);
            $entitiesNames = array();
            if($bundleName == "eDemyParamBundle") {
                //die(var_dump($metadata->getMetadata()));
            }
            foreach($metadata->getMetadata() as $entity) {
                //die(var_dump($entity->getName()));
                $name = $entity->getName();
                $parts = explode('\\', $name);
                $name = end($parts);
                $entitiesNames[] = $name;
            }

            return $entitiesNames;
        } catch (\Exception $e) {
            //TODO FileLoaderImportCircularReferenceException
            //die(var_dump($e));
            return array();
        }
    }

    public function onSearchSubQuery($searchEvent)
    {
        $query = $searchEvent['search'];
        //die(var_dump($query));
        $bundleName = $this->getBundleName(false);
        if($bundleName == 'Product') {
            $params = $this->getParamByType('config', $this->getNamespace(), $this->getBundleName());
            if(count($params)) {
                $bundleEntities = $this->getBundleEntities($this->getBundleName());
                foreach($bundleEntities as $entityName) {
                    foreach($params as $param) {
                        if(($param->getName() == 'search_entity') and ($param->getValue() == $entityName)) {
                            //search entities with query
                            $entities = $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName() . ':' . $entityName)->findBySearchQuery($query, $this->getNamespace());
                            foreach($entities as $entity) {
                                $result = $this->render(strtolower($entityName) . '_search_result.html.twig', array(
                                    'entity' => $entity,
                                ));
                                $searchEvent['results'] = array_merge($searchEvent['results'], array($result));
                            }
                        }
                    }
                }
            }
        }
        //$entity = $em->getRepository($this->getBundleName().':' . $this->getEntityNameUpper())->findOneBy(array(
        //    'id' => $id,
            //'namespace' => $this->getNamespace(),
        //));
        //$items = $this->getParamByType($menu, $namespace, $this->getBundleName());
        //if($items) {
            //$searchEvent['results'] = array_merge($searchEvent['results'], array($this->getBundleName()));
            //$searchEvent['results'][] = array($this->getBundleName());
        //}

        return true;
    }

    public function getLastModifiedFiles($dir, $name)
    {
        $lastmodified = null;
        $reflection = new \ReflectionClass(get_class($this));
        if(strpos($reflection->getFileName(), 'app/cache/')) {
            $basedir = dirname($reflection->getFileName()) . '/../../..';
        } else {
            $basedir = dirname($reflection->getFileName()) . '/../../../../../..';
        }
        $finder = new Finder();
        $finder
            ->files()
            ->in($basedir . $dir)
            ->name($name);
            //->sortByModifiedTime();
        foreach ($finder as $file) {
            $lastmodified_files = new \DateTime();
            $lastmodified_files = \DateTime::createFromFormat( 'U', $file->getMTime() );
            if($lastmodified_files > $lastmodified) {
                $lastmodified = $lastmodified_files;
            }
        }
        
        return $lastmodified;
    }

    public function start($name, $section = null)
    {
        if($this->isDevelopment()) {
            //if($section) $this->stopwatch->openSection();
            $this->stopwatch->start($name, $section);
            //if($section) $this->stopwatch->stopSection($section);
        }
    }

    public function stop($name, $section = null)
    {
        if($this->isDevelopment()) {
            $this->stopwatch->stop($name);
        }
    }

    public function isProduction()
    {
        return "prod" == $this->environment;
    }

    public function isDevelopment()
    {
        return "dev" == $this->environment;
    }
}
