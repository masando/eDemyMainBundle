<?php

namespace eDemy\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use eDemy\MainBundle\Entity\BaseEntity;

/**
 * @ORM\Table()
 * @ORM\Entity
 */
class Notfound extends BaseEntity
{
    /**
     * @ORM\Column(name="url", type="string", length=255)
     */
    protected $url;

    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function showUrlInPanel()
    {
        return true;
    }

    public function showUrlInForm()
    {
        return true;
    }

    /**
     * @ORM\Column(name="redirect", type="string", length=255, nullable=true)
     */
    protected $redirect;

    public function setRedirect($redirect)
    {
        $this->redirect = $redirect;

        return $this;
    }

    public function getRedirect()
    {
        return $this->redirect;
    }

    public function showRedirectInPanel()
    {
        return true;
    }

    public function showRedirectInForm()
    {
        return true;
    }

    /**
     * @ORM\Column(name="options", type="text", nullable=true)
     */
    protected $options;

    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
