<?php

declare(strict_types=1);

namespace App\Tests\Repository;

use App\DataFixtures\UserExtraFixtures;
use App\Repository\PodcastRepository;
use App\Repository\UserRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ServiceTestCase;

class PodcastRepositoryTest extends ServiceTestCase {
    private const SEARCH_QUERY = 'title';

    private ?PodcastRepository $repo = null;

    public function testSetUp() : void {
        $this->assertInstanceOf(PodcastRepository::class, $this->repo);
    }

    public function testIndexQuery() : void {
        $query = $this->repo->indexQuery();
        $this->assertCount(8, $query->execute());
    }

    public function testIndexUserQuery() : void {
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user_with_access = $userRepository->findOneByEmail(UserExtraFixtures::USER_WITH_ACCESS['username']);
        $user_without_access = $userRepository->findOneByEmail(UserFixtures::USER['username']);

        $query = $this->repo->indexUserQuery($user_with_access);
        $this->assertCount(8, $query->execute());

        $query = $this->repo->indexUserQuery($user_without_access);
        $this->assertCount(0, $query->execute());
    }

    public function testSearchQuery() : void {
        $query = $this->repo->searchQuery(self::SEARCH_QUERY);
        $this->assertCount(8, $query->execute());
    }

    public function testSearchUserQuery() : void {
        $userRepository = self::getContainer()->get(UserRepository::class);
        $user_with_access = $userRepository->findOneByEmail(UserExtraFixtures::USER_WITH_ACCESS['username']);
        $user_without_access = $userRepository->findOneByEmail(UserFixtures::USER['username']);

        $query = $this->repo->searchUserQuery($user_with_access, self::SEARCH_QUERY);
        $this->assertCount(8, $query->execute());

        $query = $this->repo->searchUserQuery($user_without_access, self::SEARCH_QUERY);
        $this->assertCount(0, $query->execute());
    }

    protected function setUp() : void {
        parent::setUp();
        $this->repo = self::getContainer()->get(PodcastRepository::class);
    }
}
