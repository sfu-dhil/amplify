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
            $fixture->setPreserved(0 === $i % 2);
            $fixture->setDate(new \DateTime("2020-{$i}-{$i}"));
            $fixture->setRunTime("00:{$i}5:00");
            $fixture->setTitle('Title ' . $i);
            $fixture->setAlternativeTitle('AlternativeTitle ' . $i);
            $fixture->setTags(['Tags ' . $i]);
            $fixture->setBibliography("<p>This is paragraph {$i}</p>");
            $fixture->setCopyright("<p>This is paragraph {$i}</p>");
            $fixture->setTranscript("<p>This is paragraph {$i}</p>");
            $fixture->setAbstract("<p>This is paragraph {$i}</p>");
            $fixture->setSeason($this->getReference('season.1'));
            $fixture->setPodcast($this->getReference('podcast.1'));
            $fixture->addSubject('Subject ' . $i);
            $fixture->addSubject('Subject ' . ($i + 1));
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
        ];
    }
}
