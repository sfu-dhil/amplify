<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Person;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Nines\MediaBundle\Entity\Link;

class PersonFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
    public static function getGroups() : array {
        return ['dev', 'test'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Person();
            $fixture->setFullname('Fullname ' . $i);
            $fixture->setSortableName('SortableName ' . $i);
            $fixture->setLocation('Location ' . $i);
            $fixture->setBio("<p>This is paragraph {$i}</p>");
            $fixture->setInstitution($this->getReference('institution.1'));
            $em->persist($fixture);
            $this->setReference('person.' . $i, $fixture);
            $em->flush();

            $link = new Link();
            $link->setUrl('http://example.com/' . $i);
            $em->persist($link);
            $fixture->setLinks([$link]);
            $em->flush();
        }

        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [
            InstitutionFixtures::class,
        ];
    }
}
