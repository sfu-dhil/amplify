<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\UserRepository;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class UserRepositoryTest extends ServiceTestCase {
    private const SEARCH_QUERY = 'user';

    private ?UserRepository $repo = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(UserRepository::class, $this->repo);
    }

    public function testTypeaheadQuery() : void {
        $query = $this->repo->typeaheadQuery(self::SEARCH_QUERY);
        $this->assertCount(4, $query->execute());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->repo = self::getContainer()->get(UserRepository::class);
    }
}
