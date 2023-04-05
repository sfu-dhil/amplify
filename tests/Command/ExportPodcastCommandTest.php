<?php

declare(strict_types=1);

namespace App\Tests\Command;

use Nines\UtilBundle\TestCase\CommandTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use ZipArchive;

class ExportPodcastCommandTest extends CommandTestCase {
    public function testExecutePodcastMods() : void {
        $fileSystem = self::getContainer()->get(Filesystem::class);
        $filePath = dirname(__FILE__, 3) . '/data/test/exports/5.zip';

        $this->assertFalse($fileSystem->exists($filePath));

        $this->execute('app:export:podcast', [
            'podcastId' => 6,
            'format' => 'mods',
            'exportId' => 5,
        ]);

        $this->assertTrue($fileSystem->exists($filePath));

        $finder = new Finder();
        $zip = new ZipArchive;
        $this->assertTrue($zip->open($filePath));

        $expectedDir =  dirname(__FILE__, 2) . '/data/export_formats/mods';
        $finder->files()->in($expectedDir);
        $expectedFiles = [];
        foreach ($finder as $file) {
            $expectedFiles []= $file->getRelativePathname();
            $zipContent = $zip->getFromName($file->getRelativePathname());
            $this->assertNotFalse($zipContent, "MODS export file {$file->getRelativePathname()} doesn't exist in zip");
            $this->assertTrue(sha1($zipContent) == sha1_file($file->getRealpath()), "MODS export file {$file->getRelativePathname()} zip contents don't match expected contents");
        }
        for ($i = 0; $i < $zip->count(); $i++) {
            $zipFile = $zip->getNameIndex($i);
            $this->assertContains($zipFile, $expectedFiles, "MODS export unexpected file {$zipFile} found in zip");
        }
        $zip->close();
    }

    protected function setUp() : void {
        parent::setUp();
    }
}
