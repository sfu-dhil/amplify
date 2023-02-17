<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\DataFixtures;

use App\Entity\Season;
use App\Entity\Export;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Service\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ExportFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
    public static function getGroups() : array {
        return ['dev', 'test'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Export();
            if ($i % 4 === 0) {
                $fixture->setPendingStatus();
            } elseif ($i % 4 === 1) {
                $fixture->setWorkingStatus();
            } elseif ($i % 4 === 2) {
                $fixture->setSuccessStatus();
            } elseif ($i % 4 === 3) {
                $fixture->setFailureStatus();
            }
            $fixture->setMessage("Message {$i}");
            $fixture->setFormat('default');
            $fixture->setSeason($this->getReference("season.{$i}"));
            $em->persist($fixture);
            $em->flush();

            $this->setReference('export.' . $i, $fixture);
        }

        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [
            SeasonFixtures::class,
        ];
    }
}
