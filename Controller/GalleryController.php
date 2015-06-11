<?php

namespace eDemy\MainBundle\Controller;

use Symfony\Component\EventDispatcher\GenericEvent;
use eDemy\MainBundle\Event\ContentEvent;
use eDemy\MainBundle\Entity\Param;

class GalleryController extends BaseController
{
    public static function getSubscribedEvents()
    {
        return self::getSubscriptions('main', ['imagen'], array(
            'edemy_gallery_imagen_details'  => array('onImagenDetails', 0),
            'edemy_mainmenu'                => array('onGalleryMainMenu', 0),
        ));
    }

    public function onGalleryMainMenu(GenericEvent $menuEvent) {
        $items = array();
        if ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            $item = new Param($this->get('doctrine.orm.entity_manager'));
            $item->setName('Admin_Gallery');
            $item->setValue('edemy_main_imagen_index');
            $items[] = $item;
        }

        $menuEvent['items'] = array_merge($menuEvent['items'], $items);

        return true;
    }

    public function onImagenFrontpage(ContentEvent $event)
    {
        $this->get('edemy.meta')->setTitlePrefix("Galería de Imágenes");

        $this->addEventModule($event, "templates/gallery", array(
            'entities' => $this->getRepository($event->getRoute())->findBy(array(
                'namespace' => $this->getNamespace(),
            ))
        ));
    }

    public function onImagenDetails(ContentEvent $event) {
        $entity = $this->getRepository($event->getRoute())->findOneBy(array(
            'slug'        => $this->getRequestParam('slug'),
            'namespace' => $this->getNamespace(),
        ));
        $this->get('edemy.meta')->setTitlePrefix($entity->getName());

        $this->addEventModule($event, "templates/imagen_details", array(
            'entity' => $entity,
        ));
    }
    
    public function generateAction($w = null, $h = null)
    {
        if(($w != null) and ($h == null)) {
            $h = $w;
        }
        
        $im = imagecreatetruecolor($w,$h);
        for($i = 0; $i < $w; $i++) {
            for($j = 0; $j < $h; $j++) {
                $color = imagecolorallocate($im, rand(0,255), rand(0,255), rand(0,255));
                imagesetpixel($im, $i, $j, $color);
            }
        }    
        header('Content-Type: image/png');
        imagepng($im);
        //$response = $this->newResponse();
        //$response->headers->set('Content-Type', 'image/png');
        
        //return $response;
    }
}
