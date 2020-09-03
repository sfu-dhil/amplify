<?php

namespace App\DataFixtures;

use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Episode();

            $fixture->setNumber('Number ' . $i);
            $fixture->setPreserved('Preserved ' . $i);
            $fixture->setDate('Date ' . $i);
            $fixture->setRunTime('RunTime ' . $i);
            $fixture->setTitle('Title ' . $i);
            $fixture->setAlternativeTitle('AlternativeTitle ' . $i);
            $fixture->setTags('Tags ' . $i);
            $fixture->setBiblography('Biblography ' . $i);
            $fixture->setCopyright('Copyright ' . $i);
            $fixture->setTranscript('Transcript ' . $i);
            $fixture->setAbstract('Abstract ' . $i);
            $fixture->setSeason($this->getReference('season.1'));
            $fixture->setPodcast($this->getReference('podcast.1'));
            $em->persist($fixture);
            $this->setReference('episode.' . $i, $fixture);
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
            SeasonFixtures::class,
            PodcastFixtures::class,
        ];
    }

}
