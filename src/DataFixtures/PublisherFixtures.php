<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Publisher;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;

class PublisherFixtures extends Fixture implements FixtureGroupInterface {
    public static function getGroups() : array {
        return ['dev', 'test'];
    }

    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Publisher();
            $fixture->setName('Name ' . $i);
            $fixture->setLocation('Location ' . $i);
            $fixture->setWebsite('Website ' . $i);
            $fixture->setDescription("<p>This is paragraph {$i}</p>");
            $fixture->setContact("<p>This is paragraph {$i}</p>");
            $fixture->setCreated(new \DateTimeImmutable('2023-05-25'));
            $fixture->setUpdated(new \DateTimeImmutable('2023-05-25'));
            $em->persist($fixture);
            $this->setReference('publisher.' . $i, $fixture);
        }

        $em->flush();
    }
}
