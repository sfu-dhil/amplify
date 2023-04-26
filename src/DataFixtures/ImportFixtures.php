<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Import;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImportFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
    public static function getGroups() : array {
        return ['dev', 'test'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 8; $i++) {
            $fixture = new Import();
            if (0 === $i % 4) {
                $fixture->setPendingStatus();
            } elseif (1 === $i % 4) {
                $fixture->setWorkingStatus();
            } elseif (2 === $i % 4) {
                $fixture->setSuccessStatus();
            } elseif (3 === $i % 4) {
                $fixture->setFailureStatus();
            }
            $fixture->setRss("https://rss.com/{$i}");
            $fixture->setProgress($i * 25);
            $fixture->setMessage("Message {$i}");
            $fixture->setPodcast($this->getReference("podcast.{$i}"));
            $em->persist($fixture);
            $em->flush();

            $this->setReference('import.' . $i, $fixture);
        }

        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [
            PodcastFixtures::class,
        ];
    }
}
