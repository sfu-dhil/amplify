<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\EpisodeRepository;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class EpisodeRepositoryTest extends ServiceTestCase {
    private ?EpisodeRepository $repo = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(EpisodeRepository::class, $this->repo);
    }

    protected function setUp() : void {
        parent::setUp();
        $this->repo = self::getContainer()->get(EpisodeRepository::class);
    }
}
