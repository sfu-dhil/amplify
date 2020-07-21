<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\DataFixtures;

use App\Entity\Person;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PersonFixtures extends Fixture {
    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Person();

            $fixture->setFullname('New Fullname ' . $i);
            $fixture->setSortableName('New SortableName ' . $i);
            $fixture->setAffiliation('New Affiliation ' . $i);
            $fixture->setLocation('New Location ' . $i);
            $fixture->setBio('New Bio ' . $i);
            $fixture->setLinks(['http://example.com/' . $i]);

            $em->persist($fixture);
            $this->setReference('person.' . $i, $fixture);
        }

        $em->flush();
    }
}
