<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\PersonRepository;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class PersonRepositoryTest extends ServiceTestCase {
    private const TYPEAHEAD_QUERY = 'fullname';

    private ?PersonRepository $repo = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(PersonRepository::class, $this->repo);
    }

    public function testIndexQuery() : void {
        $query = $this->repo->indexQuery();
        $this->assertCount(4, $query->execute());
    }

    public function testTypeaheadQuery() : void {
        $query = $this->repo->typeaheadQuery(self::TYPEAHEAD_QUERY);
        $this->assertCount(4, $query->execute());
    }

    public function testSearchQuery() : void {
        $query = $this->repo->searchQuery(self::TYPEAHEAD_QUERY);
        $this->assertCount(4, $query->execute());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->repo = self::getContainer()->get(PersonRepository::class);
    }
}
