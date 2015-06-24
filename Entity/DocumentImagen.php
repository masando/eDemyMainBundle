<?php

namespace eDemy\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use eDemy\MainBundle\Entity\BaseImagen;

/**
 * @ORM\Table("DocumentImagen")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class DocumentImagen extends BaseImagen
{
    public function __construct($em = null)
    {
        parent::__construct($em);
    }

    public function __toString()
    {
        return $this->getWebPath();
    }

    /**
     * @ORM\ManyToOne(targetEntity="eDemy\MainBundle\Entity\Document", inversedBy="imagenes")
     */
    protected $document;

    public function setDocument($document)
    {
        $this->document = $document;

        return $this;
    }

    public function getDocument()
    {
        return $this->document;
    }
}
