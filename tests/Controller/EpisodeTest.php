<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserExtraFixtures;
use App\Repository\EpisodeRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class EpisodeTest extends ControllerTestCase {
    private const SEARCH_QUERY = 'title';

    public function testShow() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/podcasts/2/episodes/1');
            $this->assertResponseIsSuccessful();
            $this->assertSame(1, $crawler->filter('.page-actions')->selectLink('Edit')->count());
        }
    }

    public function testEdit() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/edit');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $formCrawler = $this->client->request('GET', '/podcasts/2/episodes/1/edit');
            $this->assertResponseIsSuccessful();

            $form = $formCrawler->selectButton('Update')->form([
                'episode[number]' => 10,
                'episode[date]' => '2020-01-01',
                'episode[runTime]' => '00:09:20',
                'episode[title]' => 'Updated Title',
                'episode[subTitle]' => 'Updated subTitle',
                'episode[bibliography]' => '<p>Updated Text</p>',
                'episode[transcript]' => '<p>Updated Text</p>',
                'episode[description]' => '<p>Updated Text</p>',
                'episode[permissions]' => '<p>Updated Text</p>',
            ]);
            $this->overrideField($form, 'episode[season]', '2');

            $this->client->submit($form);
            $this->assertResponseRedirects('/podcasts/2/episodes/1', Response::HTTP_FOUND);
            $responseCrawler = $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
        }
    }

    public function testNew() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        $episodeNumber = 10;
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $formCrawler = $this->client->request('GET', '/podcasts/2/episodes/new');
            $this->assertResponseIsSuccessful();

            $form = $formCrawler->selectButton('Create')->form([
                'episode[number]' => $episodeNumber,
                'episode[date]' => '2020-01-01',
                'episode[runTime]' => '00:09:20',
                'episode[title]' => 'Updated Title',
                'episode[subTitle]' => 'Updated subTitle',
                'episode[bibliography]' => '<p>Updated Text</p>',
                'episode[transcript]' => '<p>Updated Text</p>',
                'episode[description]' => '<p>Updated Text</p>',
                'episode[permissions]' => '<p>Updated Text</p>',
            ]);
            $this->overrideField($form, 'episode[season]', '2');

            $this->client->submit($form);
            $this->assertResponseRedirects('/podcasts/2', Response::HTTP_FOUND);
            $responseCrawler = $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
            $episodeNumber++;
        }
    }

    public function testDelete() : void {
        $repo = self::getContainer()->get(EpisodeRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserExtraFixtures::USER_WITH_ACCESS);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/3');
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form[action="/podcasts/2/episodes/3"]')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/podcasts/2', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }
}
