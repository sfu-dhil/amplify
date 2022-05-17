<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\DataFixtures;

use App\Entity\Season;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Service\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class SeasonFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
    public const IMAGE_FILES = [
        '28213926366_4430448ff7_c.jpg',
        '30191231240_4010f114ba_c.jpg',
        '33519978964_c025c0da71_c.jpg',
        '3632486652_b432f7b283_c.jpg',
        '49654941212_6e3bb28a75_c.jpg',
    ];

    private ?ImageManager $imageManager = null;

    public static function getGroups() : array {
        return ['dev', 'test'];
    }

    /**
     * @required
     */
    public function setImageManager(ImageManager $imageManager) : void {
        $this->imageManager = $imageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        $this->imageManager->setCopy(true);
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Season();
            $fixture->setNumber($i);
            $fixture->setPreserved(0 === $i % 2);
            $fixture->setTitle('Title ' . $i);
            $fixture->setAlternativeTitle('AlternativeTitle ' . $i);
            $fixture->setDescription("<p>This is paragraph {$i}</p>");
            $fixture->setPodcast($this->getReference('podcast.1'));
            $fixture->setPublisher($this->getReference('publisher.1'));
            $em->persist($fixture);
            $em->flush();

            $imageFile = self::IMAGE_FILES[$i];
            $upload = new UploadedFile(dirname(__FILE__, 3) . '/tests/data/image/' . $imageFile, $imageFile, 'image/jpeg', null, true);
            $image = new Image();
            $image->setFile($upload);
            $image->setPublic(0 === $i % 2);
            $image->setOriginalName($imageFile);
            $image->setDescription("<p>This is paragraph {$i}</p>");
            $image->setLicense("<p>This is paragraph {$i}</p>");
            $image->setEntity($fixture);
            $em->persist($image);
            $em->flush();

            $this->setReference('season.' . $i, $fixture);
        }

        $em->flush();
        $this->imageManager->setCopy(false);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [
            PodcastFixtures::class,
            PublisherFixtures::class,
        ];
    }
}
