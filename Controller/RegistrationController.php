<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\RegistrationController as BaseController;

class RegistrationController extends BaseController
{
    public function registerTargetAction($_target_path, Request $request)
    {
        $session = $request->getSession();
        $session->set('_target_path', $_target_path);
        //die(var_dump($session));

        $response = parent::registerAction($request);

        //die(var_dump($response));
        return $response;
    }
}
