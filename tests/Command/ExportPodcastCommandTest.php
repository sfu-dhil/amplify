<?php

declare(strict_types=1);

namespace App\Tests\Command;

use Nines\UtilBundle\TestCase\CommandTestCase;
use Symfony\Component\Filesystem\Filesystem;

class ExportPodcastCommandTest extends CommandTestCase {
    public function testExecutePodcastMods() : void {
        $fileSystem = self::getContainer()->get(Filesystem::class);
        $filePath = dirname(__FILE__, 3) . '/data/test/exports/4.zip';

        $this->assertFalse($fileSystem->exists($filePath));

        $this->execute('app:export:podcast', [
            'podcastId' => 6,
            'format' => 'mods',
            'exportId' => 4,
        ]);

        $this->assertTrue($fileSystem->exists($filePath));
    }

    public function testExecutePodcastBepress() : void {
        $fileSystem = self::getContainer()->get(Filesystem::class);
        $filePath = dirname(__FILE__, 3) . '/data/test/exports/5.zip';

        $this->assertFalse($fileSystem->exists($filePath));

        $this->execute('app:export:podcast', [
            'podcastId' => 6,
            'format' => 'bepress',
            'exportId' => 5,
        ]);

        $this->assertTrue($fileSystem->exists($filePath));
    }

    public function testExecutePodcastIslandora() : void {
        $fileSystem = self::getContainer()->get(Filesystem::class);
        $filePath = dirname(__FILE__, 3) . '/data/test/exports/6.zip';

        $this->assertFalse($fileSystem->exists($filePath));

        $this->execute('app:export:podcast', [
            'podcastId' => 6,
            'format' => 'islandora',
            'exportId' => 6,
        ]);

        $this->assertTrue($fileSystem->exists($filePath));
    }

    protected function setUp() : void {
        parent::setUp();
    }
}
