<?php

namespace App\DataFixtures;

use App\Entity\ContributorRole;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ContributorRoleFixtures extends Fixture {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new ContributorRole();

            $fixture->setName('New Name ' . $i);
            $fixture->setLabel('New Label ' . $i);
            $fixture->setDescription('New Description ' . $i);

            $em->persist($fixture);
            $this->setReference('contributorrole.' . $i, $fixture);
        }

        $em->flush();
    }

}
