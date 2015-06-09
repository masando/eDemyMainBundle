<?php

namespace eDemy\MainBundle\Controller;

use eDemy\MainBundle\Controller\BaseController;
use eDemy\MainBundle\Event\ContentEvent;
use eDemy\ParamBundle\Entity\Param;

class LogoController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('logo', [], array(
            'edemy_header_module'       => array('onHeaderModule', 0),
            //'edemy_logo_logo_show'      => array('onLogoShow', 0),
            'edemy_logo_logo_edit'      => array('onLogoEdit', 0),
        ));
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
        //die(var_dump($logo));
        if($logo != "none") {
            //$width = $this->getParam('logo.width');
            //$height = $this->getParam('logo.height');
$time_start = microtime(true);
            $this->addEventModule($event, 'logo_show.html.twig',  array(
                'logo' => $logo,
                //'width' => $width,
                //'height' => $height,
            ));
$time_end = microtime(true);
//die(var_dump(($time_end - $time_start)));
        }
        return true;
    }

    public function onLogoEdit(ContentEvent $event)
    {
        $namespace = $this->getNamespace();

        //die(var_dump($namespace));
        $this->em = $this->get('doctrine.orm.entity_manager');
        $request = $this->getCurrentRequest();
        if($namespace) {
            $route_edit = $namespace . '.' . 'edemy_logo_logo_edit';
            //$route_edit = 'edemy_logo_logo_edit';
            $route_show = $namespace . '.' . 'edemy_logo_logo_show';
        } else {
            $route_edit = 'edemy_logo_logo_edit';
            $route_show = 'edemy_logo_logo_show';
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
            $upload_dir = __DIR__.'/../../../../web';
            $file->move($upload_dir.'/images/', $path);
            $name = 'logo';
            $logo_param = $this->em->getRepository('eDemyParamBundle:Param')->findOneBy(
                array(
                    'type' => 'config',
                    'name' => $name,
                    'namespace' => $namespace,
                ));

            if (!$logo_param) {
                //create logo param
                $logo_param = new Param();
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
            
            $event->setContent($this->newRedirectResponse('edemy_logo_logo_show'));
            $event->stopPropagation();
            
            return true;
        }

        $event->addModule(
            $this->render("logo_edit.html.twig", array(
                'edit_form'   => $form->createView(),
            ))
        );

        return true;
    }
}
