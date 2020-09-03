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

            $fixture->setPublic('Public ' . $i);
            $fixture->setOriginalName('OriginalName ' . $i);
            $fixture->setAudioPath('AudioPath ' . $i);
            $fixture->setMimeType('MimeType ' . $i);
            $fixture->setFileSize('FileSize ' . $i);
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
        return [];
    }

}
