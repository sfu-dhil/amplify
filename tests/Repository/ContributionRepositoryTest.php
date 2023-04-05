<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\ContributionRepository;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class ContributionRepositoryTest extends ServiceTestCase {
    private ?ContributionRepository $repo = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(ContributionRepository::class, $this->repo);
    }

    protected function setUp() : void {
        parent::setUp();
        $this->repo = self::getContainer()->get(ContributionRepository::class);
    }
}
