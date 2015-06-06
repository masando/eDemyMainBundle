<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use FOS\UserBundle\Controller\SecurityController as BaseController;

class SecurityController extends BaseController
{
    public function loginTargetAction($_target_path, Request $request)
    {
        $session = $request->getSession();
        $session->set('_target_path', $_target_path);
        $response = parent::loginAction($request);

        return $response;
    }
}
