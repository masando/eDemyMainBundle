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

//use eDemy\MainBundle\Controller\BaseController;
use eDemy\MainBundle\Event\ContentEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * MetaController
 *
 * El servicio edemy.meta es el encargado de generar distintos meta elementos de la respuesta.

 * @author Manuel Sanchís <msanchis@edemy.es>
 */
class MetaController extends BaseController
{
    private $prefix;
    private $title;
    private $suffix;
    private $description;
    private $keywords;
    private $metas;

    /**
     * @return array Subscribed Events List
     */
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', [], array(
            'edemy_meta'            => array('onMeta', 0),
            'edemy_meta_title'       => array('onMetaTitle', 100),
            'edemy_meta_description' => array('onMetaDescription', 0),
            'edemy_meta_keywords'    => array('onMetaKeywords', 0),
        ));
    }

    public function __construct()
    {
        parent::__construct();
        $this->metas = new ArrayCollection();

    }

    /**
     * @param ContentEvent $event
     * @param $eventName
     * @param EventDispatcherInterface $dispatcher
     * @return bool
     */
    public function onMeta(ContentEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $css = null;
        $event->clearModules();
        if($dispatcher->dispatch('edemy_meta_module', $event)->isPropagationStopped()) {
            return false;
        }
        $event->setMeta(
            $this->render('snippets/join', array( 'modules' => $event->getModules() ))
        );

        return true;
    }

    /**
     * @return mixed|null|string
     */
    public function getTitlePrefix()
    {
        if($this->prefix) {
            $prefix = $this->prefix;
        } elseif ($this->getParam('titlePrefix') != 'titlePrefix') {
            $prefix = $this->getParam('titlePrefix');
        }

        if($this->getTitle() and $this->prefix) {
            return $prefix . ' | ';
        } elseif ($this->prefix) {
            return $prefix;
        }

        return null;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        $title = "";
        
        if($this->title) {
            $title .= $this->title;
        } elseif ($this->getParam('title') != 'title') {
            $title .= $this->getParam('title');
        }

        if($this->getTitleSuffix()) {
            $title .= ' | ';
        }

        return $title;
    }

    /**
     * @return mixed|null
     */
    public function getTitleSuffix()
    {
        if($this->suffix) {
            return $this->suffix;
        } elseif ($this->getParam('titleSuffix') != 'titleSuffix') {
            return $this->getParam('titleSuffix');
        }
        
        return null;
    }

    public function setTitlePrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setTitleSuffix($suffix)
    {
        $this->suffix = $suffix;
    }

    public function getFullTitle()
    {
        $title = "";
        $title .= $this->getTitlePrefix();
        $title .= $this->getTitle();
        $title .= $this->getTitleSuffix();

        return $title;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
    }

    public function getKeywords()
    {
        return $this->keywords;
    }

    public function onMetaTitle(ContentEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        $event->setTitle($this->getFullTitle());
        return true;
    }

    public function onMetaDescription(ContentEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        //TODO GET DESCRIPTION FOR ROUTE
        //$description = $this->getParam('description');
        if($this->getDescription()) {
            $event->setDescription($this->getDescription());
        }

        return true;
    }

    public function onMetaKeywords(ContentEvent $event, $eventName, EventDispatcherInterface $dispatcher)
    {
        //TODO GET KEYWORDS FOR ROUTE
        //$keywords = $this->getParam('keywords');
        if($this->getKeywords()) {
            $event->setKeywords($this->getKeywords());
        }

        return true;
    }

    public function addMeta($meta)
    {
        $this->metas->add($meta);
    }

    public function getMetas()
    {

        return $this->metas;
    }
}
