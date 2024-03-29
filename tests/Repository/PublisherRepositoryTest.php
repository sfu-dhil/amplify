<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\PodcastRepository;
use App\Repository\PublisherRepository;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class PublisherRepositoryTest extends ServiceTestCase {
    private const SEARCH_QUERY = 'name';

    private ?PublisherRepository $repo = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(PublisherRepository::class, $this->repo);
    }

    public function testIndexQuery() : void {
        $podcastRepository = self::getContainer()->get(PodcastRepository::class);
        $podcast = $podcastRepository->find(2);

        $query = $this->repo->indexQuery($podcast);
        $this->assertCount(4, $query->execute());
    }

    public function testTypeaheadQuery() : void {
        $podcastRepository = self::getContainer()->get(PodcastRepository::class);
        $podcast = $podcastRepository->find(2);

        $query = $this->repo->typeaheadQuery($podcast, self::SEARCH_QUERY);
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
        $this->repo = self::getContainer()->get(PublisherRepository::class);
    }
}
