<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\Repository\ExportRepository;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class ExportRepositoryTest extends ServiceTestCase {
    private ?ExportRepository $repo = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(ExportRepository::class, $this->repo);
    }

    protected function setUp() : void {
        parent::setUp();
        $this->repo = self::getContainer()->get(ExportRepository::class);
    }
}
