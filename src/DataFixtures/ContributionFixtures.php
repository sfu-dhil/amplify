<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

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

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Contribution();

            $fixture->setPerson($this->getReference('person.1'));
            $fixture->setContributorrole($this->getReference('contributorrole.1'));
            $fixture->setPodcast($this->getReference('podcast.1'));
            $fixture->setSeason($this->getReference('season.1'));
            $fixture->setEpisode($this->getReference('episode.1'));
            $em->persist($fixture);
            $this->setReference('contribution.' . $i, $fixture);
        }

        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
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
