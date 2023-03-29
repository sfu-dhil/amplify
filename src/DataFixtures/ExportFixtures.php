<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Export;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ExportFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
    public static function getGroups() : array {
        return ['dev', 'test'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Export();
            if (0 === $i % 4) {
                $fixture->setPendingStatus();
            } elseif (1 === $i % 4) {
                $fixture->setWorkingStatus();
            } elseif (2 === $i % 4) {
                $fixture->setSuccessStatus();
            } elseif (3 === $i % 4) {
                $fixture->setFailureStatus();
            }
            $fixture->setMessage("Message {$i}");
            $fixture->setFormat('default');
            $fixture->setSeason($this->getReference("season.{$i}"));
            $em->persist($fixture);
            $em->flush();

            $this->setReference('export.' . $i, $fixture);
        }

        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [
            SeasonFixtures::class,
        ];
    }
}
