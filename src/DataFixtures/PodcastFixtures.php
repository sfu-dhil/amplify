<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\DataFixtures;

use App\Entity\Podcast;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PodcastFixtures extends Fixture implements DependentFixtureInterface {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Podcast();

            $fixture->setTitle('New Title ' . $i);
            $fixture->setAlternativeTitle('New AlternativeTitle ' . $i);
            $fixture->setExplicit($i % 2 === 0);
            $fixture->setDescription('New Description ' . $i);
            $fixture->setCopyright('New Copyright ' . $i);
            $fixture->setCategory('New Category ' . $i);
            $fixture->setWebsite('New Website ' . $i);
            $fixture->setRss('New Rss ' . $i);
            $fixture->setTags(['New Tags ' . $i]);
            $fixture->setPublisher($this->getReference('publisher.' . $i));

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
