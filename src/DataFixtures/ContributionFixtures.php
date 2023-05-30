<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Contribution;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ContributionFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
    public static function getGroups() : array {
        return ['dev', 'test'];
    }

    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 8; $i++) {
            $fixture = new Contribution();

            $fixture->setPerson($this->getReference('person.1'));
            $fixture->setContributorrole($this->getReference('contributorrole.1'));
            $fixture->setPodcast($this->getReference($i < 4 ? 'podcast.1' : 'podcast.5'));
            $fixture->setSeason($this->getReference($i < 4 ? 'season.1' : 'season.5'));
            $fixture->setEpisode($this->getReference($i < 4 ? 'episode.1' : 'episode.5'));
            $fixture->setCreated(new \DateTimeImmutable('2023-05-25'));
            $fixture->setUpdated(new \DateTimeImmutable('2023-05-25'));
            $em->persist($fixture);
            $this->setReference('contribution.' . $i, $fixture);
        }

        $em->flush();
    }

    public function getDependencies() {
        return [
            PersonFixtures::class,
            ContributorRoleFixtures::class,
            PodcastFixtures::class,
            SeasonFixtures::class,
            EpisodeFixtures::class,
        ];
    }
}
