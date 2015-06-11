<?php
namespace eDemy\MainBundle\Handler;

use Symfony\Component\Security\Http\Logout\LogoutSuccessHandlerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LogoutHandler implements LogoutSuccessHandlerInterface
{
    protected $router;
    protected $session;

    public function setRouter($router) {
        $this->router = $router;
    }

    public function setSession($session) {
        $this->session = $session;
    }

    public function onLogoutSuccess( Request $request )
    {
        $redirect = $this->router->generate('edemy_main_frontpage');
        $response =  new RedirectResponse($redirect);

        $response->headers->addCacheControlDirective( 'no-cache', true );
        $response->headers->addCacheControlDirective( 'max-age', 0 );
        $response->headers->addCacheControlDirective( 'must-revalidate', true );
        $response->headers->addCacheControlDirective( 'no-store', true );
//        $this->session->getFlashBag()->add('reload', '1');


        return $response;
    }
}