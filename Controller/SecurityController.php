<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use eDemy\MainBundle\Event\ContentEvent;
use FOS\UserBundle\Controller\SecurityController as FOSSecurityController;
use Symfony\Component\Security\Core\SecurityContext;

class SecurityController extends FOSSecurityController
{
    public function loginTargetAction($_target_path, Request $request)
    {
        $session = $request->getSession();
        $session->set('_target_path', $_target_path);
        $response = parent::loginAction($request);

        return $response;
    }

    /**
     * Renders the login template with the given parameters. Overwrite this function in
     * an extended controller to provide additional data for the login template.
     *
     * @param array $data
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    protected function renderLogin(array $data)
    {
        //$template = sprintf('FOSUserBundle:Security:login.html.%s', $this->container->getParameter('fos_user.template.engine'));
        $edemyMain = $this->container->get('edemy.main');
        $content = $edemyMain->render('templates/main/security/login_form', $data);
        $response = $edemyMain->newResponse($content);

        return $response;
    }
}
