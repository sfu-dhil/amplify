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
    public function load(ObjectManager $em) {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Person();

            $fixture->setFullname('Fullname ' . $i);
            $fixture->setSortableName('SortableName ' . $i);
            $fixture->setLocation('Location ' . $i);
            $fixture->setBio('Bio ' . $i);
            $fixture->setLinks('Links ' . $i);
            $fixture->setInstitution($this->getReference('institution.1'));
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
            InstitutionFixtures::class,
        ];
    }

}
