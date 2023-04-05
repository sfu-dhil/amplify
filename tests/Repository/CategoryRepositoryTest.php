<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\CategoryRepository;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class CategoryRepositoryTest extends ServiceTestCase {
    private const SEARCH_QUERY = 'label';

    private ?CategoryRepository $repo = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(CategoryRepository::class, $this->repo);
    }

    public function testIndexQuery() : void {
        $query = $this->repo->indexQuery();
        $this->assertCount(4, $query->execute());
    }

    public function testTypeaheadQuery() : void {
        $query = $this->repo->typeaheadQuery(self::SEARCH_QUERY);
        $this->assertCount(4, $query->execute());
    }

    public function testSearchQuery() : void {
        $query = $this->repo->searchQuery(self::SEARCH_QUERY);
        $this->assertCount(4, $query->execute());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->repo = self::getContainer()->get(CategoryRepository::class);
    }
}
