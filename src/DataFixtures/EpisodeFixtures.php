<?php

namespace App\DataFixtures;

use App\Entity\Audio;
use App\Entity\Episode;
use DateTime;
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

            $fixture->setNumber($i);
            $fixture->setPreserved($i % 2 == 0);
            $fixture->setDate(new \DateTime("2020-{$i}-{$i}"));
            $fixture->setRunTime(1800 + ($i+1)*10);
            $fixture->setTitle('New Title ' . $i);
            $fixture->setAlternativeTitle('New AlternativeTitle ' . $i);
            $fixture->setTags(['New Tags ' . $i]);
            $fixture->setReferences('New Reference ' . $i);
            $fixture->setCopyright('New Copyright ' . $i);
            $fixture->setTranscript('New Transcript ' . $i);
            $fixture->setAbstract('New Abstract ' . $i);

            $fixture->addSubject($this->getReference('subject.' . $i));
            $fixture->addLanguage($this->getReference('language.' . $i));
            $fixture->setPodcast($this->getReference('podcast.' . $i));
            if ($i % 2 == 0) {
                $fixture->setSeason($this->getReference('season.' . $i));
            }

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
            SubjectFixtures::class,
            LanguageFixtures::class,
            PodcastFixtures::class,
            SeasonFixtures::class,
        ];
    }

}
