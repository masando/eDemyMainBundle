<?php

namespace eDemy\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
//use Gedmo\Translatable\Translatable;
//use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
//use Symfony\Component\HttpFoundation\File\UploadedFile;
//use eDemy\MainBundle\Entity\BaseEntity;
use FOS\UserBundle\Entity\User as BaseUser;

/**
 * @ORM\Table(name="fos_user")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity()
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function __construct()
    {
        parent::__construct();
        // your own logic
    }
}
