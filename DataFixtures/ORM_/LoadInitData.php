<?php

namespace eDemy\MainBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use eDemy\ParamBundle\Entity\Param;

class LoadInitData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $param = new Param();
        //$param->setType('eDemyMainBundle.config');
        //$param->setName('eDemyMainBundle.logo');
        //$param->setValue('none');
        //$param->setDescription('Main Logo. If set to none, logo disabled');

        //$manager->persist($param);
        //$manager->flush();
    }
} 
