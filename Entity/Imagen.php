<?php

namespace eDemy\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use eDemy\MainBundle\Entity\BaseImagen;

/**
 * @ORM\Table("GalleryImagen")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Imagen extends BaseImagen
{
    public function __construct($em = null)
    {
        parent::__construct($em);
    }

    public function __toString()
    {
        return $this->getWebPath();
    }
    
}
