<?php

namespace eDemy\MainBundle\Controller;

use eDemy\MainBundle\Event\ContentEvent;

class AnalyticsController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('analytics');
    }

    public function onFrontpage(ContentEvent $event)
    {
        $this->get('edemy.meta')->setTitlePrefix("Analytics");
    }
}
