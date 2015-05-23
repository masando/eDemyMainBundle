<?php
namespace eDemy\MainBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

use eDemy\MainBundle\Entity\Param;

class RoutingLoader extends Loader
{
    /** @var KernelInterface $this->kernel */
    protected $kernel;
    /** @var ContainerInterface $this->container */
    protected $container;

    public function setKernel(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
    }

    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

//    public function __construct(ContainerInterface $container, Kernel $kernel)
//    {
//        $this->container = $container;
//        $this->kernel = $kernel;
//    }

    public function load($resource, $type = null)
    {
        $edemyMain = $this->container->get('edemy.main');
        $edemyMain->start('routing_loader', 'init');
        // @TODO Comment
        $collection = new RouteCollection();
        $entitiesCollection = new RouteCollection();

        $bundles = $this->kernel->getBundles();
        foreach($bundles as $bundle) {
            $bundle = get_class($bundle);    
            if (preg_match('/eDemy/',$bundle)) {
                if(method_exists($bundle, "getBundleName")) {
                    $bundleName = call_user_func($bundle . "::getBundleName");
                    $bundleNameSimple = $bundleName;
                    $bundleNameSimple = str_replace("eDemy", "", $bundleNameSimple);
                    $bundleNameSimple = str_replace("Bundle", "", $bundleNameSimple);

                    //BUNDLE FRONTPAGE
                    if($bundleNameSimple != 'Main') {
                        $route = new Route('/{_locale}/' . strtolower($bundleNameSimple), array(
                            '_controller' => 'edemy.main:indexAction',
                            '_locale' => 'es',
                        ), array( '_locale' => 'es|en' ), array(), '', array(), array('GET', 'POST'));
                        $entitiesCollection->add('edemy_' . strtolower($bundleNameSimple) . '_frontpage', $route);
                    }
                    //$entities = array();
                    // @TODO CircularReference
                    $entities = $this->container->get('edemy.main')->getBundleEntities($bundleName);
                    if($bundleNameSimple == "Param") {
                        //die(var_dump($entities));
                    }
                    foreach($entities as $entityName) {

                        //PRODUCT FRONTPAGE
                        $route = new Route('/{_locale}/' . strtolower($bundleNameSimple) . '/' . strtolower($entityName), array(
                            '_controller' => 'edemy.main:indexAction',
                        ), array( '_locale' => 'es|en' ), array(), '', array(), array('GET'));
                        $entitiesCollection->add('edemy_' . strtolower($bundleNameSimple) . '_' . strtolower($entityName) . '_frontpage', $route);

                        //INDEX
                        $route = new Route('/admin/{_locale}/' . strtolower($bundleNameSimple) . '/' . strtolower($entityName), array(
                            '_controller' => 'edemy.main:indexAction',
                        ), array( '_locale' => 'es|en' ), array(), '', array(), array('GET'));
                        $entitiesCollection->add('edemy_' . strtolower($bundleNameSimple) . '_' . strtolower($entityName) . '_index', $route);

                        //SHOW
                        $route = new Route('/admin/{_locale}/' . strtolower($bundleNameSimple) . '/' . strtolower($entityName) . '/{id}', array(
                            '_controller' => 'edemy.main:indexAction',
                        ), array( '_locale' => 'es|en', 'id' => '\d+' ), array(), '', array(), array('GET'));
                        $entitiesCollection->add('edemy_' . strtolower($bundleNameSimple) . '_' . strtolower($entityName) . '_show', $route);

                        //NEW
                        $route = new Route('/admin/{_locale}/' . strtolower($bundleNameSimple) . '/' . strtolower($entityName) . '/new', array(
                            '_controller' => 'edemy.main:indexAction',
                        ), array( '_locale' => 'es|en' ), array(), '', array(), array('GET'));
                        $entitiesCollection->add('edemy_' . strtolower($bundleNameSimple) . '_' . strtolower($entityName) . '_new', $route);

                        //EDIT
                        $route = new Route('/admin/{_locale}/' . strtolower($bundleNameSimple) . '/' . strtolower($entityName) . '/{id}/edit', array(
                            '_controller' => 'edemy.main:indexAction',
                        ), array( '_locale' => 'es|en', 'id' => '\d+' ), array(), '', array(), array('GET'));
                        $entitiesCollection->add('edemy_' . strtolower($bundleNameSimple) . '_' . strtolower($entityName) . '_edit', $route);

                        //CREATE
                        $route = new Route('/admin/{_locale}/' . strtolower($bundleNameSimple) . '/' . strtolower($entityName), array(
                            '_controller' => 'edemy.main:indexAction',
                        ), array( '_locale' => 'es|en' ), array(), '', array(), array('POST'));
                        $entitiesCollection->add('edemy_' . strtolower($bundleNameSimple) . '_' . strtolower($entityName) . '_create', $route);

                        //UPDATE
                        $route = new Route('/admin/{_locale}/' . strtolower($bundleNameSimple) . '/' . strtolower($entityName) . '/{id}', array(
                            '_controller' => 'edemy.main:indexAction',
                        ), array( '_locale' => 'es|en', 'id' => '\d+' ), array(), '', array(), array('PUT'));
                        $entitiesCollection->add('edemy_' . strtolower($bundleNameSimple) . '_' . strtolower($entityName) . '_update', $route);

                        //DELETE
                        $route = new Route('/admin/{_locale}/' . strtolower($bundleNameSimple) . '/' . strtolower($entityName) . '/{id}', array(
                            '_controller' => 'edemy.main:indexAction',
                        ), array( '_locale' => 'es|en', 'id' => '\d+' ), array(), '', array(), array('DELETE'));
                        $entitiesCollection->add('edemy_' . strtolower($bundleNameSimple) . '_' . strtolower($entityName) . '_delete', $route);
                    }
                    $collection->addCollection($entitiesCollection);

                    $resource = "@" . $bundleName . "/Resources/config/routing.yml";
                    $type = 'yaml';
                    try {
                        $importedRoutes = $this->import($resource, $type);
                        $collection->addCollection($importedRoutes);

                        // @TODO CircularReference
                        /** @var Param[] $prefixes */
                        $prefixes = $this->container->get('edemy.main')->getParamByType('prefix');
                        if(count($prefixes)) {
                            foreach($prefixes as $prefix) {
                                //$prefixRoutes = new RouteCollection();
                                $prefixRoutes = clone $entitiesCollection;
                                $prefixRoutes->addCollection($this->import($resource, $type));
                                $prefixRoutes->addPrefix($prefix->getValue());
                                
                                foreach($prefixRoutes as $oldName => $route) {
                                    $newName = $prefix->getValue() . '.' . $oldName;
                                    $prefixRoutes->add($newName, $route);
                                    $prefixRoutes->remove($oldName);
                                }
                                $collection->addCollection($prefixRoutes);
                            }
                        }
                    }
//                    catch (\FileLoaderLoadException $e) {
//                        // @TODO LOG ERROR
//                    }
                    catch (\InvalidArgumentException $e) {
                        // @TODO LOG ERROR
                    }
                }
            }
        }
        $edemyMain->stop('routing_loader', 'init');
        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'extra';
    }
} 
