<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\DataFixtures;

use App\Entity\Episode;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Episode();

            $fixture->setNumber($i);
            $fixture->setDate(new \DateTime("2020-{$i}-{$i}"));
            $fixture->setRunTime($i * 60 * 60 + $i);
            $fixture->setTitle('New Title ' . $i);
            $fixture->setAlternativeTitle('New AlternativeTitle ' . $i);
            $fixture->setLanguage('New Language ' . $i);
            $fixture->setTags(['New Tags ' . $i]);
            $fixture->setReferences('New References ' . $i);
            $fixture->setCopyright('New Copyright ' . $i);
            $fixture->setTranscript('New Transcript ' . $i);
            $fixture->setAbstract('New Abstract ' . $i);
            $fixture->setPodcast($this->getReference('podcast.' . $i));
            $fixture->setSeason($this->getReference('season.' . $i));
            $fixture->addSubject($this->getReference('subject.' . $i));

            $em->persist($fixture);
            $this->setReference('episode.' . $i, $fixture);
        }

        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [
            SeasonFixtures::class,
            PodcastFixtures::class,
            SubjectFixtures::class,
        ];
    }
}
