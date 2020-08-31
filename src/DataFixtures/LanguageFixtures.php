<?php

namespace App\DataFixtures;

use App\Entity\Language;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LanguageFixtures extends Fixture {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em)
    {
        for($i = 0; $i < 4; $i++) {
            $fixture = new Language();

        $fixture->setName('New Name ' . $i);
            $fixture->setLabel('New Label ' . $i);
            $fixture->setDescription('New Description ' . $i);

            $em->persist($fixture);
            $this->setReference('language.' . $i, $fixture);
        }

        $em->flush();
    }

}
