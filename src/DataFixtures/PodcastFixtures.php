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
            $fixture->setTitle('Title ' . $i);
            $fixture->setAlternativeTitle('AlternativeTitle ' . $i);
            $fixture->setExplicit(0 === $i % 2);
            $fixture->setDescription("<p>This is paragraph {$i}</p>");
            $fixture->setCopyright("<p>This is paragraph {$i}</p>");
            $fixture->setWebsite("<p>This is paragraph {$i}</p>");
            $fixture->setRss('Rss ' . $i);
            $fixture->setTags(['Tags ' . $i]);
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
        return [
            PublisherFixtures::class,
        ];
    }
}
