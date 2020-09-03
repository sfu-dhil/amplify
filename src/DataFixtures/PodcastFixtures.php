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
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Podcast();

            $fixture->setTitle('Title ' . $i);
            $fixture->setAlternativeTitle('AlternativeTitle ' . $i);
            $fixture->setExplicit('Explicit ' . $i);
            $fixture->setDescription('Description ' . $i);
            $fixture->setCopyright('Copyright ' . $i);
            $fixture->setWebsite('Website ' . $i);
            $fixture->setRss('Rss ' . $i);
            $fixture->setTags('Tags ' . $i);
            $fixture->setPublisher($this->getReference('publisher.1'));
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
            PublisherFixtures::class,
        ];
    }

}
