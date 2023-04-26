<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\PodcastRepository;
use App\Repository\SeasonRepository;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class SeasonRepositoryTest extends ServiceTestCase {
    private const SEARCH_QUERY = 'title';

    private ?SeasonRepository $repo = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(SeasonRepository::class, $this->repo);
    }

    public function testTypeaheadQuery() : void {
        $podcastRepository = self::getContainer()->get(PodcastRepository::class);
        $podcast = $podcastRepository->find(2);

        $query = $this->repo->typeaheadQuery($podcast, self::SEARCH_QUERY);
        $this->assertCount(4, $query->execute());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->repo = self::getContainer()->get(SeasonRepository::class);
    }
}
