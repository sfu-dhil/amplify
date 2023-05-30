<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\PodcastRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class PodcastTest extends ControllerTestCase {
    private const SEARCH_QUERY = 'title';

    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/podcasts');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertSame(0, $crawler->filter('.page-actions')->selectLink('New')->count());
    }

    public function testUserIndex() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts');
        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->filter('.page-actions')->selectLink('New')->count());
    }

    public function testAdminIndex() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts');
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('.page-actions')->selectLink('New')->count());
    }

    public function testAnonIndexSearch() : void {
        $crawler = $this->client->request('GET', '/podcasts');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testUserIndexSearch() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Search')->form([
            'q' => 'podcast',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminIndexSearch() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('Search')->form([
            'q' => 'podcast',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/podcasts/2');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testUserShow() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2');
        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->filter('.page-actions')->selectLink('Edit')->count());
        $this->assertSame(0, $crawler->selectLink('New Episode')->count());
        $this->assertSame(0, $crawler->selectLink('New Season')->count());
        $this->assertSame(4, $crawler->filter('div[role="tablist"] span[role="tab"]')->count());
        foreach (range(1, 4) as $seasonId) {
            $expectedEpisodes = 2 === $seasonId ? 4 : 0;
            $this->assertSame(1, $crawler->filter("#nav-season-{$seasonId}-tab")->count());
            $this->assertSame(0, $crawler->filter("#nav-season-{$seasonId} .season-actions")->selectLink('Edit')->count());
            $this->assertSame($expectedEpisodes, $crawler->filter("#nav-season-{$seasonId} table tbody tr")->count());
            $this->assertSame(0, $crawler->filter("#nav-season-{$seasonId} table tbody tr")->selectLink('Edit')->count());
        }
    }

    public function testAdminShow() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/2');
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('.page-actions')->selectLink('Edit')->count());
        $this->assertSame(1, $crawler->selectLink('New Episode')->count());
        $this->assertSame(1, $crawler->selectLink('New Season')->count());
        $this->assertSame(4, $crawler->filter('div[role="tablist"] span[role="tab"]')->count());
        foreach (range(1, 4) as $seasonId) {
            $expectedEpisodes = 2 === $seasonId ? 4 : 0;
            $this->assertSame(1, $crawler->filter("#nav-season-{$seasonId}-tab")->count());
            $this->assertSame(1, $crawler->filter("#nav-season-{$seasonId} .season-actions")->selectLink('Edit')->count());
            $this->assertSame($expectedEpisodes, $crawler->filter("#nav-season-{$seasonId} table tbody tr")->count());
            $this->assertSame($expectedEpisodes, $crawler->filter("#nav-season-{$seasonId} table tbody tr")->selectLink('Edit')->count());
        }
    }

    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/podcasts/1/edit');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserEdit() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/1/edit');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEdit() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/podcasts/1/edit');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Update')->form([
            'podcast[title]' => 'Updated Title',
            'podcast[subTitle]' => 'Updated subTitle',
            'podcast[explicit]' => 1,
            'podcast[description]' => '<p>Updated Text</p>',
            'podcast[copyright]' => '<p>Updated Text</p>',
            'podcast[license]' => '<p>Updated Text</p>',
            'podcast[website]' => 'https://example.com',
            'podcast[rss]' => 'https://example.com',
        ]);
        $this->overrideField($form, 'podcast[publisher]', '2');

        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/1', Response::HTTP_FOUND);
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/podcasts/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserNew() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNew() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/podcasts/new');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Create')->form([
            'podcast[title]' => 'Updated Title',
            'podcast[subTitle]' => 'Updated subTitle',
            'podcast[explicit]' => 1,
            'podcast[description]' => '<p>Updated Text</p>',
            'podcast[copyright]' => '<p>Updated Text</p>',
            'podcast[license]' => '<p>Updated Text</p>',
            'podcast[website]' => 'https://example.com',
            'podcast[rss]' => 'https://example.com',
        ]);
        $this->overrideField($form, 'podcast[publisher]', '2');

        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/9', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAdminDelete() : void {
        $repo = self::getContainer()->get(PodcastRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/1');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Delete')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/podcasts', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }
}
