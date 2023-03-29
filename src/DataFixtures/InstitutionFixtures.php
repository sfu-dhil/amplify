<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Institution;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class InstitutionFixtures extends Fixture implements FixtureGroupInterface {
    public static function getGroups() : array {
        return ['dev', 'test'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Institution();
            $fixture->setCountry('Country ' . $i);
            $fixture->setName('Name ' . $i);

            $em->persist($fixture);
            $this->setReference('institution.' . $i, $fixture);
        }

        $em->flush();
    }
}
