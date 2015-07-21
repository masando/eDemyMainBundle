<?php

namespace eDemy\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use eDemy\MainBundle\Entity\BaseEntity;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;


/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="eDemy\MainBundle\Entity\TodoRepository")
 */
class Todo extends BaseEntity
{
    public function __construct($em = null)
    {
        parent::__construct($em);
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
}
