<?php

namespace App\DataFixtures;

use App\Entity\Contribution;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;


class ContributionFixtures extends Fixture implements DependentFixtureInterface {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Contribution();


            $fixture->setPerson($this->getReference('person.1'));
            $fixture->setContributorrole($this->getReference('contributorRole.1'));
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
        // add dependencies here, or remove this
        // function and "implements DependentFixtureInterface" above
        return [
            PersonFixtures::class,
            ContributorRoleFixtures::class,
            PodcastFixtures::class,
            SeasonFixtures::class,
            EpisodeFixtures::class,
        ];
    }

}
