<?php

namespace eDemy\MainBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;
use JMS\Serializer\Annotation as SER;
use Anh\Taggable\TaggableInterface;
use Anh\Taggable\AbstractTaggable;

/**
 * @SER\ExclusionPolicy("all")
 */
abstract class BaseEntity extends AbstractTaggable implements Translatable, TaggableInterface
//abstract class BaseEntity implements Translatable
{
    protected $fields;
    protected $associations;
    protected $em;
    /* @SER\Expose */
    protected $type;

    public function __construct($em = null)
    {
//        $this->type = get_class($this);
        //die($this->type);
        if($em) {
            $this->setEntityManager($em);
            $this->setMappings();
        }
    }

    //// ENTITY MANAGER
    public function setEntityManager($em)
    {
        $this->em = $em;
    }
    
    public function getEntityManager()
    {
        return $this->em;
    }


    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    public function getId()
    {
        return $this->id;
    }

    public function showIdInPanel()
    {
        return true;
    }

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
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
     * @ORM\Column(name="namespace", type="string", length=255, nullable=true)
     */
    protected $namespace;

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @ORM\Column(name="published", type="boolean", nullable=true)
     */
    protected $published;

    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    public function isPublished()
    {
        return $this->published;
    }

    public function showPublishedInForm()
    {
        return true;
    }

    public function showPublishedInPanel()
    {
        return true;
    }
    
    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="meta_description", type="text", nullable=true)
     */
    protected $meta_description;

    public function setMetaDescription($meta_description)
    {
        $this->meta_description = $meta_description;

        return $this;
    }

    public function getMetaDescription()
    {
        if($this->meta_description != null) {
            return $this->meta_description;
        } else {
            if($this->getName() != null) {
                return $this->getName();
            }
            //if($this->getDescription() != null) {
            //    return $this->getDescription();
            //}
        }
        
        return null;
    }

    /**
     * @Gedmo\Translatable
     * @ORM\Column(name="meta_keywords", type="text", nullable=true)
     */
    protected $meta_keywords;

    public function setMetaKeywords($meta_keywords)
    {
        $this->meta_keywords = $meta_keywords;

        return $this;
    }

    public function getMetaKeywords()
    {
        if($this->meta_keywords != null) {
            return $this->meta_keywords;
        } else {
            if($this->getName() != null) {
                return $this->getName();
            }
        }
        return null;
    }

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", type="string", length=255, nullable=true)
     */
    protected $slug;

    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @ORM\Column(name="orden", type="integer", nullable=true)
     */
    protected $orden;

    public function setOrden($orden)
    {
        $this->orden = $orden;
    }

    public function getOrden()
    {
        return $this->orden;
    }

    public function showOrdenInPanel()
    {
        return true;
    }

    public function showOrdenInForm()
    {
        return true;
    }

    /**
     * @Gedmo\Locale
     */
    protected $locale;
    
    public function getLocale()
    {
        return $this->locale;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }
    
    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $created;

    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated;
    
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @Gedmo\Timestampable(on="change", field={"name", "description", "type", "value", "published", "orden"})
     * @ORM\Column(name="content_changed", type="datetime", nullable=true)
     */
    protected $contentChanged;

    public function getContentChanged()
    {
        return $this->contentChanged;
    }
    
    //// FUNCTIONS
    public function getFieldMappings()
    {
        $metadata = $this->getEntityManager()->getClassMetadata($this->getBundleName() . ':' . $this->getEntityName());
        return $metadata->fieldMappings;
    }
    
    public function getAssociationMappings()
    {
        $metadata = $this->getEntityManager()->getClassMetadata($this->getBundleName() . ':' . $this->getEntityName());
        return $metadata->associationMappings;
    }

    public function setMappings()
    {
        $this->fields = $this->getFieldMappings();
        $this->associations = $this->getAssociationMappings();
        $vars = get_object_vars($this);
        foreach($vars as $key => $value) {
            if($key != 'metadata' and $key != 'em' and $key != 'fields' and $key != 'associations') {
                if(gettype($value) == 'object') {
                    if (get_class($value) == 'Doctrine\ORM\PersistentCollection') {
                        $this->associations[$key]['values'] = $value;
                    }
                }
                if(gettype($value) == 'object') {
                    if (get_class($value) == 'DateTime') {
                        $this->fields[$key]['value'] = $value;
                    }
                } 
                if(array_key_exists($key, $this->fields)) {
                    $this->fields[$key]['value'] = $value;
                } else {
                    if(!array_key_exists($key, $this->associations)) {
                        $this->fields[$key]['fieldName'] = $key;
                        $this->fields[$key]['type'] = 'string';
                        $this->fields[$key]['value'] = null;
                    }
                }
                /*
                foreach($fieldMappings as $field) {
                }
                foreach($associationMappings as $associationField) {
                }
                * */
            }
        }
//die(var_dump($this->fields));
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function getAssociations()
    {
        return $this->associations;
    }

    public function getEntityName()
    {
        $bundleNameParts = explode('\\', $this->getEntityClass());
        $entityName = end($bundleNameParts);
        
        return $entityName;
    }

    public function getEntityClass()
    {
        return get_class($this);
    }

    public function getBundleName($full = true)
    {
        $bundleName = explode('\\', $this->getEntityClass());
        if($full) {
            return $bundleName[0] . $bundleName[1];
        } else {
            $bundleNameSimple = explode('Bundle', $bundleName[1]);
            return $bundleNameSimple[0];
        }
    }
    
    public function devuelve($choices, $key, $sort = true)
    {
        if($key != null) {
            if(gettype($key) == 'array') {
                $r = array();
                foreach($key as $k => $v) {
                    $r[$v] = $choices[$v];
                }
                if($sort) {
                    ksort($r);
                } else {
                    krsort($r);
                }
                //die(var_dump($r));
                return $r;
            } else {
                return $choices[$key];
            }
        } else {
            return $choices;
        }
    }

    /**
     * @ORM\Column(name="user", type="integer", nullable=true)
     */
    protected $user;

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getTaggableType()
    {
        $name = strtolower($this->getBundleName(false) . '_' . $this->getEntityName());

        return $name;
    }

    public function showTagsInPanel() {

        return true;
    }
}
