<?php

namespace eDemy\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use eDemy\MainBundle\Entity\BaseEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;


/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="eDemy\MainBundle\Entity\DocumentRepository")
 */
class Document extends BaseEntity
{
    public function __construct($em = null)
    {
        parent::__construct($em);
    }

    /**
     * @ORM\Column(name="title", type="string", length=255)
     */
    protected $title;

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function showTitleInForm()
    {
        return true;
    }

    /**
     * @ORM\Column(name="content", type="text")
     */
    protected $content;

    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    public function getContent()
    {
        return $this->content;
    }

    public function getIntro()
    {
        return $this->content;
    }

    /**
     * @ORM\OneToMany(targetEntity="eDemy\MainBundle\Entity\DocumentImagen", mappedBy="document", cascade={"persist","remove"})
     */
    protected $imagenes;


    public function getImagenes()
    {
        return $this->imagenes;
    }

    public function addImagen(DocumentImagen $imagen)
    {
        $imagen->setDocument($this);
        $this->imagenes->add($imagen);
    }

    public function removeImagen(DocumentImagen $imagen)
    {
        $this->imagenes->removeElement($imagen);
        $this->getEntityManager()->remove($imagen);
    }

    public function showImagenesInPanel()
    {
        return true;
    }
}
