<?php

namespace eDemy\MainBundle\Twig;

class IsLoadedExtension extends \Twig_Extension
{
    /**
    * @var \Twig_Environment
    */
    protected $environment;

    public function initRuntime(\Twig_Environment $environment)
    {
        $this->environment = $environment;
//        die(var_dump($this->hasExtension('edemy_facebook_extension')));
    }

    /**
    * Returns a list of functions to add to the existing list.
    *
    * @return array An array of functions
    */
    public function getTests()
    {

        return array(
            new \Twig_SimpleTest('loaded', [$this, 'hasExtension']),
        );
    }

    /**
    * @param string $name
    * @return boolean
    */
    function hasExtension($name)
    {
//die(var_dump($name));
        return $this->environment->hasExtension($name);
    }

    /**
    * Returns the name of the extension.
    *
    * @return string The extension name
    */
    public function getName()
    {

        return 'edemy_isloaded_extension';
    }
}