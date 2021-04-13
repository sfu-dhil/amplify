<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\DataFixtures;

use App\Entity\Audio;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class AudioFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Audio();
            $fixture->setPublic(0 === $i % 2);
            $fixture->setOriginalName('OriginalName ' . $i);
            $fixture->setAudioPath('AudioPath ' . $i);
            $fixture->setMimeType('MimeType ' . $i);
            $fixture->setFileSize($i);
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
        return [
            EpisodeFixtures::class,
        ];
    }
}
