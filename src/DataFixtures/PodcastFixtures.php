<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Podcast;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Service\ImageManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class PodcastFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
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

    public function load(ObjectManager $em) : void {
        $this->imageManager->setCopy(true);
        for ($i = 0; $i < 8; $i++) {
            $fixture = new Podcast();
            $fixture->setTitle('Title ' . $i);
            $fixture->setSubTitle('SubTitle ' . $i);
            $fixture->setExplicit(0 === $i % 2);
            $fixture->setDescription("<p>This is paragraph {$i}</p>");
            $fixture->setCopyright("<p>This is paragraph {$i}</p>");
            $fixture->setLicense("<p>This is license {$i}</p>");
            $fixture->setWebsite("<p>This is paragraph {$i}</p>");
            $fixture->setRss("https://rss.com/{$i}");
            $fixture->setCreated(new \DateTimeImmutable('2023-05-25'));
            $fixture->setUpdated(new \DateTimeImmutable('2023-05-25'));
            $fixture->setPublisher($this->getReference('publisher.1'));
            $em->persist($fixture);
            $em->flush();

            $imageFile = self::IMAGE_FILES[$i % 4];
            $upload = new UploadedFile(dirname(__FILE__, 3) . '/tests/data/image/' . $imageFile, $imageFile, 'image/jpeg', null, true);
            $image = new Image();
            $image->setFile($upload);
            $image->setOriginalName($imageFile);
            $image->setDescription("<p>This is paragraph {$i}</p>");
            $image->setLicense("<p>This is paragraph {$i}</p>");
            $image->setCreated(new \DateTimeImmutable('2023-05-25'));
            $image->setUpdated(new \DateTimeImmutable('2023-05-25'));
            $image->setEntity($fixture);
            $em->persist($image);
            $em->flush();

            $this->setReference('podcast.' . $i, $fixture);
        }

        $em->flush();
        $this->imageManager->setCopy(false);
    }

    public function getDependencies() {
        return [
            PublisherFixtures::class,
        ];
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setImageManager(ImageManager $imageManager) : void {
        $this->imageManager = $imageManager;
    }
}
