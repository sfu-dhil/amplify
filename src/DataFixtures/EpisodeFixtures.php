<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Episode;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\Pdf;
use Nines\MediaBundle\Service\AudioManager;
use Nines\MediaBundle\Service\ImageManager;
use Nines\MediaBundle\Service\PdfManager;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class EpisodeFixtures extends Fixture implements DependentFixtureInterface, FixtureGroupInterface {
    public const AUDIO_FILES = [
        '259692__nsmusic__santur-arpegio.mp3',
        '390587__carloscarty__pan-flute-02.mp3',
        '391691__jpolito__jp-rainloop12.mp3',
        '443027__pramonette__thunder-long.mp3',
        '94934__bletort__taegum-1.mp3',
    ];

    public const IMAGE_FILES = [
        '28213926366_4430448ff7_c.jpg',
        '30191231240_4010f114ba_c.jpg',
        '33519978964_c025c0da71_c.jpg',
        '3632486652_b432f7b283_c.jpg',
        '49654941212_6e3bb28a75_c.jpg',
    ];

    public const PDFS = [
        'holmes_1.pdf',
        'holmes_2.pdf',
        'holmes_3.pdf',
        'holmes_4.pdf',
        'holmes_5.pdf',
    ];

    private ?AudioManager $audioManager = null;

    private ?ImageManager $imageManager = null;

    private ?PdfManager $pdfManager = null;

    public static function getGroups() : array {
        return ['dev', 'test'];
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $em) : void {
        $this->audioManager->setCopy(true);
        $this->imageManager->setCopy(true);
        $this->pdfManager->setCopy(true);
        for ($i = 0; $i < 4; $i++) {
            $fixture = new Episode();
            $fixture->setNumber($i);
            $fixture->setPreserved(0 === $i % 2);
            $fixture->setDate(new DateTimeImmutable("2020-{$i}-{$i}"));
            $fixture->setRunTime("00:{$i}5:00");
            $fixture->setTitle('Title ' . $i);
            $fixture->setSubTitle('SubTitle ' . $i);
            $fixture->setBibliography("<p>This is paragraph {$i}</p>");
            $fixture->setTranscript("<p>This is paragraph {$i}</p>");
            $fixture->setDescription("<p>This is paragraph {$i}</p>");
            $fixture->setPermissions("<p>This is paragraph {$i}</p>");
            $fixture->setSeason($this->getReference('season.1'));
            $fixture->setPodcast($this->getReference('podcast.1'));
            $fixture->addSubject('Subject ' . $i);
            $fixture->addSubject('Subject ' . ($i + 1));
            $em->persist($fixture);
            $em->flush();

            $audioFile = self::AUDIO_FILES[$i];
            $upload = new UploadedFile(dirname(__FILE__, 3) . '/tests/data/audio/' . $audioFile, $audioFile, 'audio/mp3', null, true);
            $audio = new Audio();
            $audio->setFile($upload);
            $audio->setPublic(0 === $i % 2);
            $audio->setOriginalName($audioFile);
            $audio->setDescription("<p>This is paragraph {$i}</p>");
            $audio->setLicense("<p>This is paragraph {$i}</p>");
            $audio->setEntity($fixture);
            $em->persist($audio);

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

            $file = self::PDFS[$i];
            $upload = new UploadedFile(dirname(__FILE__, 3) . '/tests/data/pdf/' . $file, $file, 'application/pdf', null, true);
            $pdf = new Pdf();
            $pdf->setFile($upload);
            $pdf->setPublic(0 === ($i % 2));
            $pdf->setOriginalName($file);
            $pdf->setDescription("<p>This is paragraph {$i}</p>");
            $pdf->setLicense("<p>This is paragraph {$i}</p>");
            $pdf->setEntity($fixture);
            $em->persist($pdf);

            $em->flush();
            $this->setReference('episode.' . $i, $fixture);
        }

        $this->audioManager->setCopy(false);
        $this->imageManager->setCopy(false);
        $this->pdfManager->setCopy(false);
    }

    /**
     * {@inheritdoc}
     */
    public function getDependencies() {
        return [
            SeasonFixtures::class,
            PodcastFixtures::class,
        ];
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setAudioManager(AudioManager $audioManager) : void {
        $this->audioManager = $audioManager;
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setImageManager(ImageManager $imageManager) : void {
        $this->imageManager = $imageManager;
    }

    #[\Symfony\Contracts\Service\Attribute\Required]
    public function setPdfManager(PdfManager $pdfManager) : void {
        $this->pdfManager = $pdfManager;
    }
}
