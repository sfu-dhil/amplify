<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\ExportRepository;
use App\Repository\PodcastRepository;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class ExportRepositoryTest extends ServiceTestCase {
    private const SEARCH_QUERY = 'Message';

    private ?ExportRepository $repo = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(ExportRepository::class, $this->repo);
    }

    public function testIndexQuery() : void {
        $podcastRepository = self::getContainer()->get(PodcastRepository::class);
        $podcast = $podcastRepository->find(2);

        $query = $this->repo->indexQuery($podcast);
        $this->assertCount(4, $query->execute());
    }

    public function testSearchQuery() : void {
        $podcastRepository = self::getContainer()->get(PodcastRepository::class);
        $podcast = $podcastRepository->find(2);

        $query = $this->repo->searchQuery($podcast, self::SEARCH_QUERY);
        $this->assertCount(4, $query->execute());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->repo = self::getContainer()->get(ExportRepository::class);
    }
}
