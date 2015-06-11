<?php

namespace eDemy\MainBundle\Controller;

use eDemy\MainBundle\Controller\BaseController;
use eDemy\MainBundle\Event\ContentEvent;

class GoogleController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('google', [], array(
            'edemy_google_verify'           => array('onGoogleVerify', 0),
            'edemy_google_map'              => array('onGoogleMap', 0),
            //'edemy_main_google_adsense'   => array('onGoogleAdsense', 0),
        ));
    }

    public function __construct()
    {
        parent::__construct();
        
    }

    public function onGoogleMap(ContentEvent $event)
    {
        if($this->getParam('googlemap_api_key')) {
            $map['key'] = $this->getParam('googlemap_api_key');
            $map['width'] = "100%";
            $map['height'] = "450px";
            $map['location'] = $this->getParam('googlemap_location');
            $this->addEventModule($event, "templates/map.html.twig", array(
                'map'    => $map,
            ));
        }
    }

    public function getGoogleMap()
    {
        if($this->getParam('googlemap_api_key')) {
            $map['key'] = $this->getParam('googlemap_api_key');
            $map['width'] = "100%";
            $map['height'] = "450px";
            $map['location'] = $this->getParam('googlemap_location');
            
            return $this->render("templates/map.html.twig", array(
                'map'    => $map,
            ));
        }
    }

    public function verifyAction()
    {
        $request = $this->getCurrentRequest();
        $code = $request->attributes->get('code');
        //if($code == null) $code = '123';
        $code = '123';
        return $this->newResponse("google-site-verification: google" . $code . ".html");
    }

    public function onGoogleVerify(ContentEvent $event)
    {
        $request = $this->getCurrentRequest();
        $code = $request->attributes->get('code');
        $code = 'e9ad0980e0b50d02';
        $event->setContent($this->newResponse("google-site-verification: google" . $code . ".html"))->stopPropagation();
        
        return true;
    }

    public function googleAdsense($width, $height)
    {
        $w = 336;
        $h = 280;
        return $this->render("adsense.html.twig", array(
            'ad-client' => 'pub-3523725188831893',
            'width' => $w,
            'height' => $h,
            'channel' => '3956934395',
        ));
    }

    public function onGoogleAdsense(ContentEvent $event)
    {
        //die(var_dump($this->getParam('enabled')));
        if($this->getParam('enabled') == '1') {
            $w = 336;
            $h = 280;
            $this->addEventModule($event, "templates/adsense", array(
                'ad-client' => 'pub-3523725188831893',
                'width' => $w,
                'height' => $h,
                'channel' => '3956934395',
            ));
        }
        return true;
    }
}
