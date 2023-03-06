<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

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
        $this->repo = self::$container->get(ExportRepository::class);
    }
}
