<?php

namespace eDemy\MainBundle\Twig;

use Symfony\Component\EventDispatcher\GenericEvent;

class TranslationExtension extends \Twig_Extension
{
    private $eventDispatcher;
    
    public function setEventDispatcher($eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('translate', array($this, 'translateFilter')),
        );
    }

    public function translateFilter($param, $default = null)
    {
        //TODO PEDIR SERVICIO PARA TRADUCIR
        $event = new GenericEvent(
            "translate",
            array(
                'param' => $param,
                'bundle' => null,
                'default' => $default,
                'value' => ''
            )
        );
        $this->eventDispatcher->dispatch('edemy_param', $event);
        return $event['value'];
    }

    public function getName()
    {
        return 'edemy_translation_extension';
    }
}
