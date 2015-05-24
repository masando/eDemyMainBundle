<?php

namespace eDemy\MainBundle\Controller;

//use eDemy\MainBundle\Controller\BaseController;
use eDemy\MainBundle\Event\ContentEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
//use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Finder;

class CssController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_css'     => array('onCss', 0),
            'edemy_css_lastmodified'    => array('onCssLastModified', 0),
        ));
    }

    public function indexAction($_route, $file, $_format)
    {
        //die(var_dump($this->get('templating.globals')));
        $parts = explode('.', $_route);
        if(count($parts) == 2) {
            $_route = end($parts);
        }

        $event = new ContentEvent($_route);
        //aquí Last-Modified Header
        $this->dispatch('edemy_css_lastmodified', $event);
        //die(var_dump($event));
        $lastmodified = $event->getLastModified();
        //die(var_dump($lastmodified));
        if($lastmodified != null) {
            //die();
            $response = $this->newResponse();
            $response->setLastModified($lastmodified);
            $response->setPublic();
            //die(var_dump($this->getRequest()));
            //die(var_dump($lastmodified));
            //die(var_dump($response->isNotModified($this->getRequest())));

            if ($response->isNotModified($this->getRequest())) {
                //die(var_dump($response));
                //die();

                return $response;
            }
        }
        $this->dispatch('edemy_css', $event);
        $css = $event->getCss();
        $response = $this->newResponse();
        $response->setContent($css);
        $response->headers->set('Content-Type', 'text/css');

        if($lastmodified != null) {
            $response->setLastModified($lastmodified);
        }

        if($event->getLastModified() != null) {
            $response->setLastModified($event->getLastModified());
        }
        $response->setPublic();
        //die(var_dump($response));

        return $response;

        /*
        $allparams = $this->get('doctrine.orm.entity_manager')->getRepository($this->getBundleName().':Param')->findAll();
        foreach($allparams as $param) {
            $params[$param->getName()] = $param->getValue();
        }
        $response = $this->newResponse();
        $response->setContent($this->get('templating')->render(
            $this->getBundleName().':Css:'.$file.'.css.twig',
            array(
                'params' => $params
            )
        ));
        $response->headers->set('Content-Type', 'text/css');
        return $response;
        * */
    }

    public function onCss(ContentEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $css = null;
        $event->clearModules();
        if($dispatcher->dispatch('edemy_css_module', $event)->isPropagationStopped()) {
            return false;
        }
        $event->setCss(
            $this->render('css', array( 'modules' => $event->getModules() ))
        );

        return true;
    }

    public function onCssLastModified(ContentEvent $event)
    {
        $reflection = new \ReflectionClass(get_class($this));
        if(strpos($reflection->getFileName(), 'app/cache/')) {
            $dir = dirname($reflection->getFileName()) . '/../../..';
        } else {
            $dir = dirname($reflection->getFileName()) . '/../../../../../..';
        }

        //$dir = dirname($reflection->getFileName());
        
        if(get_class($this) != "eDemy\MainBundle\Controller\MainController") {
            //die(var_dump(get_class($this)));
            //die(var_dump($dir));
        }
        
        
        $finder = new Finder();
        $finder
            ->files()
            ->in($dir . '/vendor/edemy/mainbundle/eDemy/*/Resources/views/css')
            ->name('*.css.twig')
            ->sortByModifiedTime();
        
        foreach ($finder as $file) {
            //print $file->getRealpath()."-";
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
        }
        //die();
        //print date($file->getMTime())."<br/>";
        //die(var_dump($event->getLastModified()));
    }
}
