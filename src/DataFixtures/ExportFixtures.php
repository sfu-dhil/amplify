<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Export;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;

class ExportFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
    public function __construct(
        private Filesystem $filesystem,
    ) {
    }

    public static function getGroups() : array {
        return ['dev', 'test'];
    }

    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 8; $i++) {
            $fixture = new Export();
            if (0 === $i % 4) {
                $fixture->setProgress(0);
                $fixture->setPendingStatus();
            } elseif (1 === $i % 4) {
                $fixture->setProgress(50);
                $fixture->setWorkingStatus();
            } elseif (2 === $i % 4) {
                $fixture->setProgress(100);
                $fixture->setSuccessStatus();
                $id = $i + 1;
                $fixture->setPath("{$id}.zip");
                $this->filesystem->copy(dirname(__FILE__, 3) . "/tests/data/exports/{$id}.zip", dirname(__FILE__, 3) . "/data/test/exports/{$id}.zip");
            } elseif (3 === $i % 4) {
                $fixture->setProgress(75);
                $fixture->setFailureStatus();
            }
            $fixture->setMessage("Message {$i}");
            $fixture->setFormat('mods');
            $fixture->setCreated(new \DateTimeImmutable('2023-05-25'));
            $fixture->setUpdated(new \DateTimeImmutable('2023-05-25'));
            $fixture->setPodcast($this->getReference($i < 4 ? 'podcast.1' : 'podcast.5'));
            $em->persist($fixture);
            $em->flush();

            $this->setReference('export.' . $i, $fixture);
        }

        $em->flush();
    }

    public function getDependencies() {
        return [
            PodcastFixtures::class,
        ];
    }
}
