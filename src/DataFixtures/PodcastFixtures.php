<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Person;
use App\Entity\Podcast;

use App\Entity\Publisher;
use App\Entity\Share;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Service\ImageManager;
use Nines\UserBundle\DataFixtures\UserFixtures;
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
            $fixture->addKeyword('Keyword ' . $i);
            $fixture->setCreated(new DateTimeImmutable('2023-05-25'));
            $fixture->setUpdated(new DateTimeImmutable('2023-05-25'));
            $em->persist($fixture);
            $em->flush();

            for ($j = 0; $j < 4; $j++) {
                $person = new Person();
                $person->setPodcast($fixture);
                $person->setFullname('Fullname ' . $i . ' - ' . $j);
                $person->setSortableName('SortableName ' . $i . ' - ' . $j);
                $person->setLocation('Location ' . $i . ' - ' . $j);
                $person->setBio("<p>This is paragraph {$i} - {$j}</p>");
                $person->setCreated(new DateTimeImmutable('2023-05-25'));
                $person->setUpdated(new DateTimeImmutable('2023-05-25'));
                $person->setInstitution('Institution ' . $i . ' - ' . $j);
                $em->persist($person);
                $em->flush();
            }

            for ($j = 0; $j < 4; $j++) {
                $publisher = new Publisher();
                $publisher->setPodcast($fixture);
                $publisher->setName('Name ' . $i . ' - ' . $j);
                $publisher->setLocation('Location ' . $i . ' - ' . $j);
                $publisher->setWebsite('Website ' . $i . ' - ' . $j);
                $publisher->setDescription("<p>This is paragraph {$i} - {$j}</p>");
                $publisher->setContact("<p>This is paragraph {$i} - {$j}</p>");
                $publisher->setCreated(new DateTimeImmutable('2023-05-25'));
                $publisher->setUpdated(new DateTimeImmutable('2023-05-25'));
                $em->persist($publisher);
                $em->flush();

                if (0 === $j) {
                    $fixture->setPublisher($publisher);
                    $em->flush();
                }
            }

            $imageFile = self::IMAGE_FILES[$i % 4];
            $upload = new UploadedFile(dirname(__FILE__, 3) . '/tests/data/image/' . $imageFile, $imageFile, 'image/jpeg', null, true);
            $image = new Image();
            $image->setFile($upload);
            $image->setOriginalName($imageFile);
            $image->setDescription("<p>This is paragraph {$i}</p>");
            $image->setLicense("<p>This is paragraph {$i}</p>");
            $image->setCreated(new DateTimeImmutable('2023-05-25'));
            $image->setUpdated(new DateTimeImmutable('2023-05-25'));
            $image->setEntity($fixture);
            $em->persist($image);
            $em->flush();

            $share = new Share();
            $share->setPodcast($fixture);
            $share->setUser($this->getReference('user.user_access'));
            $em->persist($share);
            $em->flush();

            $this->setReference('podcast.' . $i, $fixture);
        }

        $em->flush();
        $this->imageManager->setCopy(false);
    }

    public function getDependencies() {
        return [
            UserFixtures::class,
            UserExtraFixtures::class,
        ];
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setImageManager(ImageManager $imageManager) : void {
        $this->imageManager = $imageManager;
    }
}
