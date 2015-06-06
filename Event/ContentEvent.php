<?php

namespace eDemy\MainBundle\Event;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\EventDispatcher\Event;
use Doctrine\Common\Collections\ArrayCollection;

class ContentEvent extends Event
{
    protected $route;
    protected $format;
    protected $css;
    protected $js;
    protected $title;
    protected $description;
    protected $keywords;
    protected $meta;
    protected $header;
    protected $logo;
    protected $content;
    protected $footer;
    protected $javascript;
    protected $modules;
    protected $mode;
    protected $lastmodified;
    protected $routeMatch;
    
    public function __construct($route = null, $routeMatch = null)
    {
        $this->route = $route;
        $this->format = 'html';
        $this->css = null;
        $this->title = null;
        $this->description = null;
        $this->keywords = null;
        $this->meta = null;
        $this->header = null;
        $this->logo = null;
        $this->content = null;
        $this->footer = null;
        $this->javascript = null;
        $this->mode = null;
        $this->lastmodified = null;
        $this->modules = new ArrayCollection();
        $this->routeMatch = $routeMatch;
    }
    
    ////CSS
    public function getCss()
    {
        return $this->css;
    }
    
    public function setCss($css)
    {
        $this->css = $css;
        return $this;
    }

    ////JS
    public function getJs()
    {
        return $this->js;
    }

    public function setJs($js)
    {
        $this->js = $js;
        return $this;
    }

    ////TITLE
    public function getTitle()
    {
        return $this->title;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    ////DESCRIPTION
    public function getDescription()
    {
        return $this->description;
    }
    
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    ////KEYWORDS
    public function getKeywords()
    {
        return $this->keywords;
    }
    
    public function setKeywords($keywords)
    {
        $this->keywords = $keywords;
        return $this;
    }

    ////META
    public function getMeta()
    {
        return $this->meta;
    }
    
    public function setMeta($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    ////HEADER
    public function getHeader()
    {
        return $this->header;
    }
    
    public function setHeader($header)
    {
        $this->header = $header;
        return $this;
    }

    ////FOOTER
    public function getFooter()
    {
        return $this->footer;
    }
    
    public function setFooter($footer)
    {
        $this->footer = $footer;
        return $this;
    }

    ////JAVASCRIPT
    public function getJavascript()
    {
        return $this->javascript;
    }
    
    public function setJavascript($javascript)
    {
        $this->javascript = $javascript;
        return $this;
    }

    ////ROUTE
    public function getRoute()
    {
        return $this->route;
    }

    public function getRouteLastModified()
    {
        return $this->route . '_lastmodified';
    }


    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    // Format
    public function getFormat()
    {
        return $this->format;
    }

    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }

    ////ROUTE
    public function getRouteMatch($item)
    {
        return $this->routeMatch[$item];
    }
    
    public function setRouteMatch($routeMatch)
    {
        $this->routeMatch = $routeMatch;
        return $this;
    }

    ////LOGO
    public function getLogo()
    {
        return $this->logo;
    }
    
    public function setLogo($logo)
    {
        $this->logo = $logo;
        return $this;
    }

    ////CONTENT
    public function getContent()
    {
        return $this->content;
    }
    
    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }
    
    ////LASTMODIFIED
    public function getLastModified()
    {
        return $this->lastmodified;
    }
    
    public function setLastModified($lastmodified)
    {
        $this->lastmodified = $lastmodified;
        return $this;
    }

    ////MODULES
    public function addModule($module)
    {
        $this->modules->add($module);
    }
    
    public function getModules()
    {
        return $this->modules;
    }

    public function clearModules()
    {
        $this->modules->clear();
    }
    
    public function getMode()
    {
        return $this->mode;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;
    }
}

