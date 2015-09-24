<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Component\EventDispatcher\GenericEvent;
use eDemy\MainBundle\Event\ContentEvent;
use eDemy\MainBundle\Entity\Param;

class LogoController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('logo', [], array(
            'edemy_header_module'       => array('onHeaderModule', 1),
            //'edemy_main_logo_show'      => array('onLogoShow', 0),
            'edemy_main_logo_edit'      => array('onLogoEdit', 0),
            'edemy_mainmenu'                => array('onLogoMainMenu', 0),
        ));
    }

    public function onLogoMainMenu(GenericEvent $menuEvent) {
        $items = array();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $item = new Param($this->get('doctrine.orm.entity_manager'));
            $item->setName('Admin_Logo');
            if($namespace = $this->getNamespace()) {
                $namespace .= ".";
            }
            $item->setValue($namespace . 'edemy_main_logo_edit');
            $items[] = $item;
        }

        $menuEvent['items'] = array_merge($menuEvent['items'], $items);

        return true;
    }

    public function onHeaderModule(ContentEvent $event)
    {
        $this->onLogoShow($event);
    }

    public function onLogoShow(ContentEvent $event)
    {
        $event->addModule($this->get('edemy.twig.logo_extension')->renderLogo());

        return null;

        $logo = $this->getParam('logo');
        //die(var_dump($this->getNamespace($_route)));
//        die(var_dump($logo));
        if($logo != "none") {
            //$width = $this->getParam('logo.width');
            //$height = $this->getParam('logo.height');
//$time_start = microtime(true);
            $this->addEventModule($event, 'templates/main/logo/logo_show',  array(
                'logo' => $logo,
                //'width' => $width,
                //'height' => $height,
            ));
//$time_end = microtime(true);
//die(var_dump(($time_end - $time_start)));
        }
        return true;
    }

    public function onLogoEdit(ContentEvent $event)
    {
        $this->container = $this->get('service_container');
        $this->denyAccessUnlessGranted('ROLE_ADMIN', null, 'No tienes permisos para acceder a este recurso!');

        $namespace = $this->getNamespace();

        //die(var_dump($namespace));
        $this->em = $this->get('doctrine.orm.entity_manager');
        $request = $this->getCurrentRequest();
        if($namespace) {
            $route_edit = $namespace . '.' . 'edemy_main_logo_edit';
            //$route_edit = 'edemy_main_logo_edit';
            $route_show = $namespace . '.' . 'edemy_main_logo_show';
        } else {
            $route_edit = 'edemy_main_logo_edit';
            $route_show = 'edemy_main_logo_show';
        }
        $form = $this->get('form.factory')->createBuilder()
            ->setAction($this->get('router')->generate($route_edit))
            ->setMethod('POST')
            ->add('logo', 'file')
            ->add('submit', 'submit', array('label' => 'Actualizar'))
            ->getForm();
        $form->handleRequest($request);

        if ($form->isValid()) {
            $file = $form['logo']->getData();
            $path = 'logo' . $namespace . '.' . $file->guessExtension();
            $host = $_SERVER['HTTP_HOST'];
            $parts = explode(".", $host);
            if(count($parts) == 3) {
                $subdomain = $parts[0];
                $domain = $parts[1] . '.' . $parts[2];
            } else {
                $domain = $parts[0] . '.' . $parts[1];
                $subdomain = 'www';
            }
            if(strpos(__DIR__, '/cache/')) {
                // subimos hasta el directorio raíz de la aplicación (3 niveles)
                $upload_dir = __DIR__ . '/../../../web';
            } else {
                // si no subimos 6 niveles hasta el directorio raíz de la aplicación
                $upload_dir = __DIR__ . '/../../../../../../web';
            }
            $upload_dir = '/var/www/'.$domain;
            //$file->move($upload_dir.'/images_'.$this->getRequest()->getHost().'/', $path);
            $file->move($upload_dir.'/images', $path);
            $name = 'logo';
            $logo_param = $this->em->getRepository('eDemyMainBundle:Param')->findOneBy(
                array(
                    'type' => 'config',
                    'name' => $name,
                    'namespace' => $namespace,
                ));

            if (!$logo_param) {
                //create logo param
                $logo_param = new Param($this->em);
            }
            $logo_param
                ->setBundle($this->getBundleName())
                ->setType('config')
                ->setName($name)
                ->setValue($path)
                ->setPublished(true)
                ->setNamespace($namespace)
            ;
            //die(var_dump($logo_param));
            $this->em->persist($logo_param);
            $this->em->flush();
            
            $event->setContent($this->newRedirectResponse('edemy_main_logo_show'));
            $event->stopPropagation();
            
            return true;
        }

        $event->addModule(
            $this->render("templates/main/logo/logo_edit", array(
                'edit_form'   => $form->createView(),
            ))
        );

        return true;
    }
}
