<?php

namespace eDemy\MainBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BaseType extends AbstractType
{
    protected $entity = null;

    public function __construct($entity)
    {
        $this->entity = $entity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $entity = $builder->getData();
        if($entity == null) {
            $entity = $this->entity;
            //die(var_dump($this->entity));
        }
        //if(get_class($entity) != "eDemy\\KeilaBundle\\Entity\\Alianza") {
            //die(var_dump($entity->getFields()));
        //}
        //die(var_dump($entity));
        $builder->add('tags', 'tags', array(
            'tagit' => array(/* ... */), // see https://github.com/hilobok/tag-it for available options, may be empty
            'autocomplete' => 'static', // default
            'required' => false,
        ));
        foreach($entity->getFields() as $field) {
            if($field['fieldName'] != 'id' 
                //and $field['fieldName'] != 'namespace'
                and $field['fieldName'] != 'fields'
                and $field['fieldName'] != 'associations'
                //and $field['fieldName'] != 'slug'
                and $field['fieldName'] != 'locale'
            ) {
                if($field['type'] == 'array') {
                    if (method_exists($entity, "get" . $field['fieldName'] . "Choices")
                        && is_callable(array($entity, "get" . $field['fieldName'] . "Choices"))) {
                        $choices = call_user_func_array(array($entity, "get" . $field['fieldName'] . "Choices"), array());
                    } else {
                        $choices = array();
                    }
                    if (method_exists($entity, "get" . $field['fieldName'] . "Multiple")
                        && is_callable(array($entity, "get" . $field['fieldName'] . "Multiple"))) {
                        $multiple = call_user_func_array(array($entity, "get" . $field['fieldName'] . "Multiple"), array());
                    } else {
                        $multiple = array();
                    }
                    //$choices = call_user_func_array(array($entity, "get" . $field['fieldName'] . "Choices"), array());
                    //$multiple = call_user_func_array(array($entity, "get" . $field['fieldName'] . "Multiple"), array());
                    if (method_exists($entity, "show" . $field['fieldName'] . "InForm")
                        && is_callable(array($entity, "show" . $field['fieldName'] . "InForm"))) {
                        $show = call_user_func_array(array($entity, "show" . $field['fieldName'] . "InForm"), array());
                    } else {
                        $show = false;
                    }
                    if($show) {
                        $builder->add($field['fieldName'], 'choice', array(
                            'choices'   => $choices,
                            'required'  => true,
                            'multiple'  => $multiple,
                        ));
                    }
                } else if($field['type'] == 'text') {
                    //die(var_dump($field['fieldName']));
                    if($field['fieldName'] == 'meta_description'
                        or $field['fieldName'] == 'meta_keywords' or (($this->entity->getEntityName() == 'Param') and ($field['fieldName'] == 'description'))) {
                            if (method_exists($entity, "show" . $field['fieldName'] . "InForm")
                                && is_callable(array($entity, "show" . $field['fieldName'] . "InForm"))) {
                                $show = call_user_func_array(array($entity, "show" . $field['fieldName'] . "InForm"), array());
                            } else {
                                $show = false;
                            }
                            if($show) {
                                $builder->add($field['fieldName'], 'textarea', array(
                                    'required' => false,
                                ));
                            }
                    } else {
                        $builder->add($field['fieldName'], 'ckeditor', array(
                            'filebrowser_image_browse_url' => array(
                                'route'            => 'elfinder',
                                'route_parameters' => array('instance' => 'ckeditor'),
                            ),
                        ));
                    }
                } else {
                    if (method_exists($entity, "show" . $field['fieldName'] . "InForm")
                        && is_callable(array($entity, "show" . $field['fieldName'] . "InForm"))) {
                        $show = call_user_func_array(array($entity, "show" . $field['fieldName'] . "InForm"), array());
                    } else {
                        $show = false;
                    }
                    if($show) {
                        if($field['fieldName'] == 'webpath') {
                            $builder->add($field['fieldName'], null, array(
                                'mapped' => false,
                            ));
                        } else {
                            $builder->add($field['fieldName'], null);
                        }
                    }
                }
            }
        }
        //die(var_dump($entity->getAssociations()));
        foreach($entity->getAssociations() as $association) {
            if(
                $association['fieldName'] == 'imagenes' or
                $association['fieldName'] == 'products'
            ) {
                $relationEntity = $association['targetEntity'];
                $e = new $relationEntity($this->entity->getEntityManager());
                $form = new \eDemy\MainBundle\Form\BaseType($e);
                $builder->add($association['fieldName'], 'collection', array(
                    'type'         => $form,
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'required'     => false,
                ));
            } elseif($association['fieldName'] == 'horarios') {
                $relationEntity = $association['targetEntity'];
                $e = new $relationEntity($this->entity->getEntityManager());
                $form = new \eDemy\MainBundle\Form\BaseType($e);
                //die(var_dump($form));
                $builder->add($association['fieldName'], 'collection', array(
                    'type'         => $form,
                    'allow_add'    => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                ));
            } elseif($association['fieldName'] == 'presupuestos') {
            } else {
                if (method_exists($entity, "show" . $association['fieldName'] . "InForm")
                    && is_callable(array($entity, "show" . $association['fieldName'] . "InForm"))) {
                    $show = call_user_func_array(array($entity, "show" . $association['fieldName'] . "InForm"), array());
                } else {
                    $show = false;
                }
                if($show) {
                    $builder->add($association['fieldName']);
                }
            }
        }
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => $this->entity->getEntityClass(),
        ));
    }

    public function getName()
    {
        //die(var_dump(strtolower($this->entity->getBundleName(false))));
        //die(var_dump('edemy_' . strtolower($this->entity->getBundleName(false)) . 'bundle_' . strtolower($this->entity->getEntityName())));
        return 'edemy_' . strtolower($this->entity->getBundleName(false)) . 'bundle_' . strtolower($this->entity->getEntityName());
    }
}
