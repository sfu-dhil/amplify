<?php

namespace App\DataFixtures;

use App\Entity\Institution;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class InstitutionFixtures extends Fixture {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Institution();

            $fixture->setProvince('New Province ' . $i);
            $fixture->setName('New Name ' . $i);

            $em->persist($fixture);
            $this->setReference('institution.' . $i, $fixture);
        }

        $em->flush();
    }

}
