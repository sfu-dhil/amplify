<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\DataFixtures;

use App\Entity\Publisher;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PublisherFixtures extends Fixture {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Publisher();

            $fixture->setName('New Name ' . $i);
            $fixture->setLocation('New Location ' . $i);
            $fixture->setWebsite('New Website ' . $i);
            $fixture->setDescription('New Description ' . $i);
            $fixture->setContact('New Contact ' . $i);

            $em->persist($fixture);
            $this->setReference('publisher.' . $i, $fixture);
        }

        $em->flush();
    }
}
