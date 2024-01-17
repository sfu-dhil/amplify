<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserExtraFixtures;
use App\Repository\PodcastRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class PodcastTest extends ControllerTestCase {
    private const SEARCH_QUERY = 'title';

    public function testIndex() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access, User with podcast access, Admin
        foreach ([UserFixtures::USER, UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/podcasts');
            $this->assertResponseIsSuccessful();
            $this->assertSame(1, $crawler->filter('.page-actions')->selectLink('Import New Podcast from RSS Feed')->count());
        }
    }

    public function testSearch() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access, User with podcast access, Admin
        foreach ([UserFixtures::USER, UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/podcasts');
            $this->assertResponseIsSuccessful();

            $form = $crawler->selectButton('Search')->form([
                'q' => 'podcast',
            ]);

            $responseCrawler = $this->client->submit($form);
            $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        }
    }

    public function testShow() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/podcasts/2');
            $this->assertResponseIsSuccessful();
            $this->assertSame(1, $crawler->filter('.page-actions')->selectLink('Edit Podcast')->count());
            $this->assertSame(2, $crawler->selectLink('Edit Podcast')->count());
            $this->assertSame(1, $crawler->selectLink('New Episode')->count());
            $this->assertSame(1, $crawler->selectLink('New Season')->count());
            $this->assertSame(4, $crawler->filter('div[role="tablist"] span[role="tab"]')->count());
            foreach (range(1, 4) as $seasonId) {
                $expectedEpisodes = 2 === $seasonId ? 4 : 0;
                $this->assertSame(1, $crawler->filter("#nav-season-{$seasonId}-tab")->count());
                $this->assertSame(1, $crawler->filter("#nav-season-{$seasonId} .season-actions")->selectLink('Edit Season')->count());
                $this->assertSame($expectedEpisodes, $crawler->filter("#nav-season-{$seasonId} table tbody tr")->count());
                $this->assertSame($expectedEpisodes, $crawler->filter("#nav-season-{$seasonId} table tbody tr")->selectLink('Edit Episode')->count());
            }
        }
    }

    public function testEdit() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/1/edit');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/1/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $formCrawler = $this->client->request('GET', '/podcasts/1/edit');
            $this->assertResponseIsSuccessful();

            $form = $formCrawler->selectButton('Save')->form([
                'podcast[title]' => 'Updated Title',
                'podcast[subTitle]' => 'Updated subTitle',
                'podcast[explicit]' => 1,
                'podcast[languageCode]' => 'en',
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
    }

    public function testDelete() : void {
        $repo = self::getContainer()->get(PodcastRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserExtraFixtures::USER_WITH_ACCESS);
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
