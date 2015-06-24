<?php

namespace eDemy\MainBundle\Twig;

//use Symfony\Component\EventDispatcher\GenericEvent;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DocumentExtension extends \Twig_Extension
{
    /** @var ContainerInterface $this->container */
    protected $container;
    
    public function setContainer(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('relatedDocument', array($this, 'relatedDocumentFunction'), array('is_safe' => array('html'), 'pre_escape' => 'html')),
        );
    }

    public function relatedDocumentFunction($entity)
    {
//        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            $repository = $this->container->get('anh_taggable.manager')->getTaggingRepository();
            $docs = new ArrayCollection();
            foreach ($entity->getTags() as $tag) {
                $ids = $repository->getResourcesWithTypeAndTag('main_document', $tag);
                foreach($ids as $id){
                    $doc = $this->container->get('doctrine.orm.entity_manager')->getRepository('eDemyMainBundle:Document')->findOneById($id);
                    if($doc) {
                        $docs->add($doc);
                    }
                }
            }
            $content = $this->container->get('edemy.main')->render('templates/main/document/related',array(
                'docs'  => $docs
            ));

            return $content;
//        }
    }

    public function getName()
    {
        return 'edemy_document_extension';
    }
}
