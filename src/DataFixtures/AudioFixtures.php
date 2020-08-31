<?php

namespace App\DataFixtures;

use App\Entity\Audio;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AudioFixtures extends Fixture implements DependentFixtureInterface {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Audio();

            $fixture->setPublic('New Public ' . $i);
            $fixture->setOriginalName('New OriginalName ' . $i);
            $fixture->setAudioPath('/path/to/audio/' . $i);
            $fixture->setMimeType('New MimeType ' . $i);
            $fixture->setFileSize(2400 * ($i+1));

            $fixture->setEpisode($this->getReference('episode.' . $i));

            $em->persist($fixture);
            $this->setReference('audio.' . $i, $fixture);
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
            EpisodeFixtures::class,
        ];
    }

}
