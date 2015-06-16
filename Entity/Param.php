<?php

namespace eDemy\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="eDemy\MainBundle\Entity\ParamRepository")
 */
class Param extends BaseEntity
{
    public function __construct($em, $bundle = null, $type = null, $name = null, $value = null, $description = null, $namespace = null)
    {
        parent::__construct($em);
        if($bundle) $this->setBundle($bundle);
        if($type) $this->setType($type);
        if($name) $this->setName($name);
        if($value) $this->setValue($value);
        if($description) $this->setDescription($description);
        if($namespace) $this->setNamespace($namespace);
    }
    
    public function __toString()
    {
        return $this->getValue();
    }

    public function showMeta_DescriptionInForm()
    {
        return false;
    }

    public function showMeta_KeywordsInForm()
    {
        return false;
    }

    /**
     * @ORM\Column(name="bundle", type="string", length=255, nullable=true)
     */
    protected $bundle;

    public function setBundle($bundle)
    {
        $this->bundle = $bundle;

        return $this;
    }

    public function getBundle()
    {
        return $this->bundle;
    }

    public function showBundleInPanel()
    {
        return true;
    }
    
    public function showBundleInForm()
    {
        return true;
    }

    /**
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    protected $type;

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getType()
    {
        return $this->type;
    }

    public function showTypeInPanel()
    {
        return true;
    }

    public function showTypeInForm()
    {
        return true;
    }

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function showNameInPanel()
    {
        return true;
    }

    public function showNameInForm()
    {
        return true;
    }

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="value", type="string", length=255, nullable=true)
     */
    protected $value;

    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function showValueInPanel()
    {
        return true;
    }

    public function showValueInForm()
    {
        return true;
    }

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }
    
    public function showDescriptionInForm()
    {
        return true;
    }

    ////
    public function showNamespaceInPanel()
    {
        return true;
    }

    public function showNamespaceInForm()
    {
        return true;
    }
}
