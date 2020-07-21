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
    public function load(ObjectManager $em)
    {
        for($i = 0; $i < 4; $i++) {
            $fixture = new Episode();

        $fixture->setNumber('New Number ' . $i);
            $fixture->setDate('New Date ' . $i);
            $fixture->setRunTime('New RunTime ' . $i);
            $fixture->setTitle('New Title ' . $i);
            $fixture->setAlternativeTitle('New AlternativeTitle ' . $i);
            $fixture->setLanguage('New Language ' . $i);
            $fixture->setTags('New Tags ' . $i);
            $fixture->setReferences('New References ' . $i);
            $fixture->setCopyright('New Copyright ' . $i);
            $fixture->setTranscript('New Transcript ' . $i);
            $fixture->setAbstract('New Abstract ' . $i);
            
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
                                                                                                                                                        ];
    }

}
