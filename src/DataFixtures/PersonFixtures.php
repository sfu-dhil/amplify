<?php

namespace App\DataFixtures;

use App\Entity\Person;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PersonFixtures extends Fixture implements DependentFixtureInterface {

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $em)
    {
        for($i = 0; $i < 4; $i++) {
            $fixture = new Person();

        $fixture->setFullname('New Fullname ' . $i);
            $fixture->setSortableName('New SortableName ' . $i);
            $fixture->setAffiliation('New Affiliation ' . $i);
            $fixture->setLocation('New Location ' . $i);
            $fixture->setBio('New Bio ' . $i);
            $fixture->setLinks('New Links ' . $i);
            
            $em->persist($fixture);
            $this->setReference('person.' . $i, $fixture);
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
