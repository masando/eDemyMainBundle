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
use eDemy\MainBundle\Entity\Param;
use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * ParamController
 *
 * El servicio edemy.param es el encargado de gestionar los parámetros de configuración.

 * @author Manuel Sanchís <msanchis@edemy.es>
 */
class ParamController extends BaseController
{
    /**
     * @return array Subscribed Events List
     */
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', ['param'], array(
            'edemy_param'               => array('onParam', 0),
            'edemy_param_by_type'       => array('onParamByType', 0),
            'edemy_main_param_index_lastmodified' => array('onParamIndexLastmodified', 0),
            'edemy_main_param_edit_lastmodified' => array('onParamEditLastmodified', 0),
            'edemy_main_param_new_lastmodified' => array('onParamNewLastmodified', 0),
            'edemy_main_param_details_lastmodified' => array('onParamDetailsLastmodified', 0),
            'edemy_mainmenu'                => array('onParamMainMenu', 0),
        ));
    }

    public function onParamMainMenu(GenericEvent $menuEvent) {
        $items = array();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $item = new Param($this->get('doctrine.orm.entity_manager'));
            $item->setName('Admin_Param');
            if($namespace = $this->getNamespace()) {
                $namespace .= ".";
            }
            $item->setValue($namespace . 'edemy_main_param_index');
            $items[] = $item;
        }

        $menuEvent['items'] = array_merge($menuEvent['items'], $items);

        return true;
    }

    public function onProductFrontpageLastModified(ContentEvent $event)
    {
        $product = $this->getRepository('edemy_product_category_index')->findLastModified($this->getNamespace());

        if($product->getUpdated()) {
            //die(var_dump($entity->getUpdated()));
            $event->setLastModified($product->getUpdated());
        }
    }

    public function onParamIndexLastmodified(ContentEvent $event) {
        $this->container = $this->get('service_container');
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $param = $this->getRepository('edemy_main_param_index')->findLastModified($this->getNamespace());
            if($param->getUpdated()) {
                $event->setLastModified($param->getUpdated());
            }
        }

        return true;
    }

    public function onParamShowLastmodified(ContentEvent $event)
    {
        $this->container = $this->get('service_container');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $request = $this->get('request_stack')->getCurrentRequest();
            $id = $request->attributes->get('id');
            $entity = $this->get('doctrine.orm.entity_manager')->getRepository('eDemyMainBundle:Param')->findOneBy(array(
                'id' => $id,
                //'namespace' => $this->getNamespace(),
            ));
            $lastmodified = $entity->getUpdated();
            $event->setLastModified($lastmodified);
        }

        return true;
    }

    public function onParamEditLastmodified(ContentEvent $event) {
        $this->container = $this->get('service_container');

        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $request = $this->get('request_stack')->getCurrentRequest();
            $id = $request->attributes->get('id');
            $entity = $this->get('doctrine.orm.entity_manager')->getRepository('eDemyMainBundle:Param')->findOneBy(array(
                'id' => $id,
                //'namespace' => $this->getNamespace(),
            ));
            $lastmodified = $entity->getUpdated();
            $event->setLastModified($lastmodified);
        }

        return true;
    }

    public function onParamNewLastmodified(ContentEvent $event)
    {
        $event->setLastModified(new \DateTime());
    }

    public function onFrontpage(ContentEvent $event) { }

    /**
     * Esta función la podemos llamar desde el servicio directamente o desde el evento onParam
     */
    public function getParamP($param, $bundle = null, $default = null, $namespace = null, $object = false)
    {
        if($bundle == "all") {
            $entities = $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName().':Param')->findBy(array(
                'name' => $param,
                'published' => true,
            ));
        } else {
            $entities = $this->get('doctrine.orm.entity_manager')
//                ->getRepository($this->getBundleName().':Param')
                ->getRepository('eDemyMainBundle:Param')
                ->findBy(array(
                    'bundle' => $bundle,
                    'name' => $param,
                    'namespace' => $namespace,
                    'published' => true,
                ));
        }
//        dump($this->getBundleName());
        if (!$entities) {
            if ($default != null) {
                $value = $default;
            } else {
                $value = $param;
            }
//            dump($param . ' ' . $default . ' ' . $value);
        } else {
            if(count($entities) == 1) {
                $value = $entities[0]->getValue();
                if($object) return $entities[0];
            } else {
                $value = $entities;
            }
        }

//        if($bundle == null) dump($this->class);|
        return $value;
    }

    public function onParam(GenericEvent $event)
    {
        if($event->getSubject() == 'translate') {
            $event['value'] = $this->getParamP($event['param'], 'all', $event['default']);
        } else {
            $event['value'] = $this->getParamP($event['param'], $event['bundle'], $event['default'], $event['namespace'], $event['object']);
            if($event['value'] == $event['param']) {
//                if($event['value'] == 'themeBundle') {
//                    die(var_dump($event));
//                }
                $event['value'] = $this->getParamP($event['param'], $event['bundle'], $event['default'], 'all', $event['object']);
//                die(var_dump($event));
            }
        }
        return true;
        /*
        $entity = $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName().':Param')->findOneBy(array(
            //'type' => $event['type'],
            'name' => $event['param']
        ));
        if (!$entity) {
            if($event['default'] != null) {
                $event['value'] = $event['default'];
            } else {
                $event['value'] = $event['param'];
            }
            return false;
        }
        $event['value'] = $entity->getValue();

        return true;
        */
    }

    public function getParamByType($type, $namespace = null, $bundle = null)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $entities_namespace = array();
        $entities_all = array();
        if($bundle != null) {
            $entities_namespace = $em->getRepository($this->getBundleName().':Param')->findBy(array(
                'type' => $type,
                'namespace' => $namespace,
                'published' => true,
                'bundle' => $bundle,
            ));
            $entities_all = $em->getRepository($this->getBundleName().':Param')->findBy(array(
                'type' => $type,
                'namespace' => 'all',
                'published' => true,
                'bundle' => $bundle,
            ));
        } else {
             //die(var_dump($em->getRepository($this->getBundleName().':Param')));
            try {
                $entities_namespace = $em->getRepository($this->getBundleName().':Param')->findBy(
                    array(
                        'type' => $type,
                        'namespace' => $namespace,
                        'published' => true,
                    )
                );
                $entities_all = $em->getRepository($this->getBundleName().':Param')->findBy(array(
                    'type' => $type,
                    'namespace' => 'all',
                    'published' => true,
                ));
            } catch(\Exception $e) {
                //die(var_dump($e));
                //return array();
            }
        }

        $entities = array_merge($entities_namespace, $entities_all);
        if($type == 'css') {
            //die(var_dump($entities));
        }
        //$entities = array_merge($entities, $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName().':Param')->findBy(array(
        //    'type' => $type,
        //    'namespace' => 'all',
        //)));
        
        if (!$entities) {
                $values = null;
        } else {
                $values = $entities;
        }

        return $values;
    }

    public function onParamByType(GenericEvent $event)
    {
        $event['values'] = $this->getParamByType($event['type'], $event['namespace'], $event['bundle']);
        return true;
    }
}
