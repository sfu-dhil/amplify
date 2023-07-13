<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\PodcastRepository;
use App\Repository\ShareRepository;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class ShareRepositoryTest extends ServiceTestCase {
    private const SEARCH_QUERY = 'user';

    private ?ShareRepository $repo = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(ShareRepository::class, $this->repo);
    }

    public function testIndexQuery() : void {
        $podcastRepository = self::getContainer()->get(PodcastRepository::class);
        $podcast = $podcastRepository->find(2);

        $query = $this->repo->indexQuery($podcast);
        $this->assertCount(1, $query->execute());
    }

    public function testSearchQuery() : void {
        $podcastRepository = self::getContainer()->get(PodcastRepository::class);
        $podcast = $podcastRepository->find(2);

        $query = $this->repo->searchQuery($podcast, self::SEARCH_QUERY);
        $this->assertCount(1, $query->execute());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->repo = self::getContainer()->get(ShareRepository::class);
    }
}
