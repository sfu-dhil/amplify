<?php

namespace App\DataFixtures;

use App\Entity\Podcast;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PodcastFixtures extends Fixture implements DependentFixtureInterface {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em)
    {
        for($i = 0; $i < 4; $i++) {
            $fixture = new Podcast();

        $fixture->setTitle('New Title ' . $i);
            $fixture->setAlternativeTitle('New AlternativeTitle ' . $i);
            $fixture->setExplicit('New Explicit ' . $i);
            $fixture->setDescription('New Description ' . $i);
            $fixture->setCopyright('New Copyright ' . $i);
            $fixture->setCategory('New Category ' . $i);
            $fixture->setWebsite('New Website ' . $i);
            $fixture->setRss('New Rss ' . $i);
            $fixture->setTags('New Tags ' . $i);
            
            $em->persist($fixture);
            $this->setReference('podcast.' . $i, $fixture);
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
