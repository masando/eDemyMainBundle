<?php

namespace eDemy\MainBundle\Controller;

use eDemy\MainBundle\Controller\BaseController;
use eDemy\MainBundle\Event\ContentEvent;

class UserController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('user', []);
    }

    public function __construct()
    {
        parent::__construct();
        
    }

}
