<?php
namespace eDemy\MainBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

class RoutingLoader extends Loader
{
    private $kernel;
    private $container;

    public function setKernel(Kernel $kernel)
    {
        $this->kernel = $kernel;
    }

    public function setContainer($container)
    {
        $this->container = $container;
    }

    public function load($resource, $type = null)
    {
        //TODO Comment
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
                        $prefixes = array();
                        $prefixes = $this->container->get('edemy.main')->getParamByType('prefix');
                        if(count($prefixes)) {
                            foreach($prefixes as $prefix) {
                                $prefixRoutes = new RouteCollection();
                                $prefixRoutes = clone $entitiesCollection;
                                $prefixRoutes->addCollection($this->import($resource, $type));
                                $prefixRoutes->addPrefix($prefix->getValue());
                                
                                foreach($prefixRoutes as $oldname => $route) {
                                    $newname = $prefix->getValue() . '.' . $oldname;
                                    $prefixRoutes->add($newname, $route);
                                    $prefixRoutes->remove($oldname);
                                }
                                $collection->addCollection($prefixRoutes);
                            }
                        }
                    }
                    catch (\FileLoaderLoadException $e) {
                        //TODO LOG ERROR
                    }
                    catch (\InvalidArgumentException $e) {
                        //TODO LOG ERROR
                    }
                }
            }
        }

        return $collection;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'extra';
    }
} 
