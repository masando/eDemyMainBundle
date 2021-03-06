<?php
namespace eDemy\MainBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

use eDemy\MainBundle\Entity\Param;

/**
 * Class RoutingLoader
 * La clase RoutingLoader se encarga de añadir, para cada bundle eDemy:
 * la ruta frontpage asociada al Bundle excepto para MainBundle
 * las rutas de las entities del Bundle
 * las rutas del archivo routing.yml del Bundle
 * las rutas FOS
 * Si hay prefijos, añadir todas las rutas anteriores con cada prefijo
 *
 * @package eDemy\MainBundle\Routing
 * @author Manuel Sanchís <msanchis@edemy.es>
 */
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

    public function load($resource, $type = null)
    {
        // medimos el tiempo de carga de rutas
//        $stopWatch = $this->container->get('debug.stopwatch');
//        $stopWatch->start('routing_loader', 'init');
        // Inicializamos las colecciones de rutas
        $allRoutes = new RouteCollection();
        $routes = new RouteCollection();
//        die('A');
//        $this->dump($this->kernel->getContainer()));
        foreach($this->kernel->getBundles() as $bundle) {
            $bundle = get_class($bundle);
            // sólo analizamos los bundles eDemy
            if (method_exists($bundle, "eDemyBundle")) {
                // Añadimos la ruta frontpage asociada al Bundle excepto para MainBundle
                $this->addBundleFrontpageRoute($bundle, $routes);
                // Añadimos las rutas de las entities del Bundle
                $this->addEntityRoutes($bundle, $routes);
                // Añadimos las rutas del archivo routing.yml del Bundle
                $this->addFileRoutes($bundle, $routes);
                // Añadimos las rutas FOS
                $this->addFOSRoutes($routes);
                // Añadimos todas las rutas anteriores a la colección principal
                $allRoutes->addCollection($routes);
                // Si hay prefijos añadimos todas las rutas anteriores con cada prefijo a la colección principal
                $this->prefixRoutes($routes, $allRoutes);
            }
        }
//        $stopWatch->stop('routing_loader', 'init');
        return $allRoutes;
    }

    public function supports($resource, $type = null)
    {
        return $type === 'extra';
    }

    public function addEntityRoutes($bundle, RouteCollection $routesCollection){
        $bundleNameSimpleLower = $bundle::getBundleName('simple');
        // Obtenemos las entities del Bundle
        $entities = $this->container->get('edemy.main')->getBundleEntities($bundle::getBundleName());

        // Para cada una de las entities generamos las rutas aociadas (frontpage y admin CRUD)
        foreach($entities as $entityName) {
            $entityNameLower = strtolower($entityName);

            // ENTITY FRONTPAGE (i.e. GET /es/agenda/actividad)
            $route = new Route('/{_locale}/' . $bundleNameSimpleLower . '/' . $entityNameLower, array(
                '_controller' => 'edemy.main:indexAction',
            ), array( '_locale' => 'es|en' ), array(), '', array(), array('GET'));
            $routesCollection->add('edemy_' . $bundleNameSimpleLower . '_' . $entityNameLower . '_frontpage', $route);

            // ENTITY INDEX (i.e. GET /admin/es/agenda/actividad)
            $route = new Route('/admin/{_locale}/' . $bundleNameSimpleLower . '/' . $entityNameLower, array(
                '_controller' => 'edemy.main:indexAction',
            ), array( '_locale' => 'es|en' ), array(), '', array(), array('GET'));
            $routesCollection->add('edemy_' . $bundleNameSimpleLower . '_' . $entityNameLower . '_index', $route);

            // ENTITY SHOW (i.e. GET /admin/es/agenda/actividad/1)
            $route = new Route('/admin/{_locale}/' . $bundleNameSimpleLower . '/' . $entityNameLower . '/{id}', array(
                '_controller' => 'edemy.main:indexAction',
            ), array( '_locale' => 'es|en', 'id' => '\d+' ), array(), '', array(), array('GET'));
            $routesCollection->add('edemy_' . $bundleNameSimpleLower . '_' . $entityNameLower . '_show', $route);

            // ENTITY NEW (i.e. GET /admin/es/agenda/actividad/new)
            $route = new Route('/admin/{_locale}/' . $bundleNameSimpleLower . '/' . $entityNameLower . '/new', array(
                '_controller' => 'edemy.main:indexAction',
            ), array( '_locale' => 'es|en' ), array(), '', array(), array('GET'));
            $routesCollection->add('edemy_' . $bundleNameSimpleLower . '_' . $entityNameLower . '_new', $route);

            // ENTITY EDIT (i.e. GET /admin/es/agenda/actividad/1/edit)
            $route = new Route('/admin/{_locale}/' . $bundleNameSimpleLower . '/' . $entityNameLower . '/{id}/edit', array(
                '_controller' => 'edemy.main:indexAction',
            ), array( '_locale' => 'es|en', 'id' => '\d+' ), array(), '', array(), array('GET'));
            $routesCollection->add('edemy_' . $bundleNameSimpleLower . '_' . $entityNameLower . '_edit', $route);

            // ENTITY EDIT PUBLISH (i.e. GET /admin/es/agenda/actividad/1/publish)
            $route = new Route('/admin/{_locale}/' . $bundleNameSimpleLower . '/' . $entityNameLower . '/{id}/publish', array(
                '_controller' => 'edemy.main:publishAction',
            ), array( '_locale' => 'es|en', 'id' => '\d+' ), array(), '', array(), array('GET'));
            $routesCollection->add('edemy_' . $bundleNameSimpleLower . '_' . $entityNameLower . '_editpublish', $route);

            // ENTITY EDIT PUBLISH (i.e. GET /admin/es/agenda/actividad/1/publish)
            $route = new Route('/admin/{_locale}/' . $bundleNameSimpleLower . '/' . $entityNameLower . '/{id}/unpublish', array(
                '_controller' => 'edemy.main:unpublishAction',
            ), array( '_locale' => 'es|en', 'id' => '\d+' ), array(), '', array(), array('GET'));
            $routesCollection->add('edemy_' . $bundleNameSimpleLower . '_' . $entityNameLower . '_editunpublish', $route);

            // ENTITY BATCH (i.e. POST /admin/es/agenda/actividad/batch)
            $route = new Route('/admin/{_locale}/' . $bundleNameSimpleLower . '/' . $entityNameLower . '/batch', array(
                '_controller' => 'edemy.main:indexAction',
            ), array( '_locale' => 'es|en' ), array(), '', array(), array('POST'));
            $routesCollection->add('edemy_' . $bundleNameSimpleLower . '_' . $entityNameLower . '_batch', $route);

            // ENTITY CREATE (i.e. POST /admin/es/agenda/actividad/1)
            $route = new Route('/admin/{_locale}/' . $bundleNameSimpleLower . '/' . $entityNameLower, array(
                '_controller' => 'edemy.main:indexAction',
            ), array( '_locale' => 'es|en' ), array(), '', array(), array('POST'));
            $routesCollection->add('edemy_' . $bundleNameSimpleLower . '_' . $entityNameLower . '_create', $route);

            // ENTITY UPDATE (i.e. PUT /admin/es/agenda/actividad/1)
            $route = new Route('/admin/{_locale}/' . $bundleNameSimpleLower . '/' . $entityNameLower . '/{id}', array(
                '_controller' => 'edemy.main:indexAction',
            ), array( '_locale' => 'es|en', 'id' => '\d+' ), array(), '', array(), array('PUT'));
            $routesCollection->add('edemy_' . $bundleNameSimpleLower . '_' . $entityNameLower . '_update', $route);

            // ENTITY DELETE (i.e. DELETE /admin/es/agenda/actividad/1)
            $route = new Route('/admin/{_locale}/' . $bundleNameSimpleLower . '/' . $entityNameLower . '/{id}', array(
                '_controller' => 'edemy.main:indexAction',
            ), array( '_locale' => 'es|en', 'id' => '\d+' ), array(), '', array(), array('DELETE'));
            $routesCollection->add('edemy_' . $bundleNameSimpleLower . '_' . $entityNameLower . '_delete', $route);
        }
    }


    public function addBundleFrontpageRoute($bundle, RouteCollection $routes) {
//        if (method_exists($bundle, "getBundleName")) {
//            $bundleNameSimpleLower = call_user_func($bundle . "::getBundleName");
//        }
        $bundleNameSimpleLower = $bundle::getBundleName('simple');

        if($bundleNameSimpleLower != 'main') {
            $route = new Route('/{_locale}/' . $bundleNameSimpleLower, array(
                '_controller' => 'edemy.main:indexAction',
                '_locale' => 'es',
            ), array( '_locale' => 'es|en' ), array(), '', array(), array('GET', 'POST'));
            $routes->add('edemy_' . $bundleNameSimpleLower . '_frontpage', $route);
        } else {
            // @TODO crear las rutas frontpage de cada controlador
            $controllerDir = __DIR__ . '/../Controller';
//            die(var_dump($controllerDir));
            $finder = new Finder();
            $finder->files()->in($controllerDir)->name('*Controller.php');
            foreach ($finder as $file) {
                $controllerNameSimpleLower = $this->getControllerName($file->getFileName());

                if($controllerNameSimpleLower != 'main') {
                    $route = new Route('/{_locale}/' . $controllerNameSimpleLower, array(
                        '_controller' => 'edemy.main:indexAction',
                        '_locale' => 'es',
                    ), array('_locale' => 'es|en'), array(), '', array(), array('GET', 'POST'));
                    $routes->add('edemy_' . $controllerNameSimpleLower . '_frontpage', $route);
                }
            }
        }
    }

    public function getControllerName($name) {

        return strtolower(str_replace("Controller.php", "", $name));
    }

    public function addFileRoutes($bundle, RouteCollection $routes) {
        $bundleName = $bundle::getBundleName();

        $resource = "@" . $bundleName . "/Resources/config/routing.yml";
        $type = 'yaml';
        $routes->addCollection($this->import($resource, $type));
    }

    public function addFOSRoutes(RouteCollection $routes) {
        $resource = "@FOSUserBundle/Resources/config/routing/security.xml";
        $type = 'xml';
        $routes->addCollection($this->import($resource, $type));

        $resource = "@FOSUserBundle/Resources/config/routing/profile.xml";
        $type = 'xml';
        $prefixRoutes = $this->import($resource, $type);
        $prefixRoutes->addPrefix('profile');
        $routes->addCollection($prefixRoutes);

        $resource = "@FOSUserBundle/Resources/config/routing/registration.xml";
        $type = 'xml';
        $prefixRoutes = $this->import($resource, $type);
        $prefixRoutes->addPrefix('register');
        $routes->addCollection($prefixRoutes);

        $resource = "@FOSUserBundle/Resources/config/routing/resetting.xml";
        $type = 'xml';
        $prefixRoutes = $this->import($resource, $type);
        $prefixRoutes->addPrefix('resetting');
        $routes->addCollection($prefixRoutes);

        $resource = "@FOSUserBundle/Resources/config/routing/change_password.xml";
        $type = 'xml';
        $prefixRoutes = $this->import($resource, $type);
        $prefixRoutes->addPrefix('profile');
        $routes->addCollection($prefixRoutes);
    }

    public function prefixRoutes(RouteCollection $routes, RouteCollection $allRoutes) {
        /** @var Param[] $prefixes */
        $prefixes = $this->container->get('edemy.main')->getParamByType('prefix');
        if(count($prefixes)) {
            foreach($prefixes as $prefix) {
                //$prefixRoutes = new RouteCollection();
                $prefixRoutes = clone $routes;
                //$prefixRoutes->addCollection($this->import($resource, $type));
                $prefixRoutes->addPrefix($prefix->getValue());

                foreach($prefixRoutes as $oldName => $route) {
                    $newName = $prefix->getValue() . '.' . $oldName;
                    $prefixRoutes->add($newName, $route);
                    $prefixRoutes->remove($oldName);
                }
                $allRoutes->addCollection($prefixRoutes);
            }
        }
    }
}
