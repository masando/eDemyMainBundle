<?php

namespace eDemy\ParamBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use eDemy\ParamBundle\Entity\Param;

class LoadInitData implements FixtureInterface
{
    private $manager;
    
    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;
        $this->addParam('eDemyParamBundle', 'translate', 'param_param.list', 'Listado de parámetros', 'Descripción de listado de parámetros', 'all');
        $this->addParam('eDemyParamBundle', 'translate', 'param_param.new', 'Nuevo Parámetro', 'Descripción de nuevo parámetros', 'all');
        $this->addParam('eDemyParamBundle', 'translate', 'param_param.edit', 'Modificar Parámetro', 'Descripción de modificar parámetros', 'all');
        $this->addParam('eDemyParamBundle', 'translate', 'param_param.show', 'Detalles de Parámetro', 'Descripción de detalles de parámetros', 'all');
        //$this->addParam('eDemyParamBundle', 'adminmenu', 'Param', 'edemy_param_param_index', 'Ruta al listado de parámetros (admin)', 'all');
    }
    
    public function addParam($bundle, $type, $name, $value, $description, $namespace)
    {
        $entity = $this->manager->getRepository('eDemyParamBundle:Param')->findOneBy(array(
            'bundle' => $bundle,
            'type' => $type,
            'name' => $name,
            'namespace' => $namespace,
        ));
        if($entity) {
            //die("a");
        } else {
            $param = new Param(null, $bundle, $type, $name, $value, $description, $namespace);

            $this->manager->persist($param);
            $this->manager->flush();
        }
    }
} 
