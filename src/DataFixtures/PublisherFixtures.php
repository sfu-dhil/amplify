<?php

namespace App\DataFixtures;

use App\Entity\Publisher;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PublisherFixtures extends Fixture implements DependentFixtureInterface {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Publisher();

            $fixture->setName('Name ' . $i);
            $fixture->setLocation('Location ' . $i);
            $fixture->setWebsite('Website ' . $i);
            $fixture->setDescription('Description ' . $i);
            $fixture->setContact('Contact ' . $i);
            $em->persist($fixture);
            $this->setReference('publisher.' . $i, $fixture);
        }

        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        // add dependencies here, or remove this
        // function and "implements DependentFixtureInterface" above
        return [];
    }

}
