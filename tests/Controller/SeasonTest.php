<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserExtraFixtures;
use App\Repository\SeasonRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class SeasonTest extends ControllerTestCase {
    private const SEARCH_QUERY = 'title';

    public function testShow() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/seasons/1');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/seasons/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/podcasts/2/seasons/1');
            $this->assertResponseIsSuccessful();
            $this->assertSame(1, $crawler->filter('.page-actions')->selectLink('Edit')->count());
        }
    }

    public function testTypeahead() : void {
        // Anon
        $this->client->request('GET', '/podcasts/2/seasons/typeahead?q=' . self::SEARCH_QUERY);
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $this->client->request('GET', '/podcasts/2/seasons/typeahead?q=' . self::SEARCH_QUERY);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $this->client->request('GET', '/podcasts/2/seasons/typeahead?q=' . self::SEARCH_QUERY);
            $response = $this->client->getResponse();
            $this->assertResponseIsSuccessful();
            $this->assertSame('application/json', $response->headers->get('content-type'));
            $json = json_decode($response->getContent());
            $this->assertCount(4, $json);
        }
    }

    public function testEdit() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/seasons/1/edit');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/seasons/1/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $formCrawler = $this->client->request('GET', '/podcasts/2/seasons/1/edit');
            $this->assertResponseIsSuccessful();

            $form = $formCrawler->selectButton('Update')->form([
                'season[number]' => 10,
                'season[title]' => 'Updated Title',
                'season[subTitle]' => 'Updated subTitle',
                'season[description]' => '<p>Updated Text</p>',
            ]);
            $this->overrideField($form, 'season[publisher]', '2');

            $this->client->submit($form);
            $this->assertResponseRedirects('/podcasts/2/seasons/1', Response::HTTP_FOUND);
            $responseCrawler = $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
        }
    }

    public function testNew() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/seasons/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/seasons/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        $seasonNumber = 10;
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $formCrawler = $this->client->request('GET', '/podcasts/2/seasons/new');
            $this->assertResponseIsSuccessful();

            $form = $formCrawler->selectButton('Create')->form([
                'season[number]' => $seasonNumber,
                'season[title]' => 'Updated Title',
                'season[subTitle]' => 'Updated subTitle',
                'season[description]' => '<p>Updated Text</p>',
            ]);
            $this->overrideField($form, 'season[publisher]', '2');

            $this->client->submit($form);
            $this->assertResponseRedirects('/podcasts/2', Response::HTTP_FOUND);
            $responseCrawler = $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
            $seasonNumber++;
        }
    }

    public function testDelete() : void {
        $repo = self::getContainer()->get(SeasonRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserExtraFixtures::USER_WITH_ACCESS);
        $crawler = $this->client->request('GET', '/podcasts/2/seasons/1');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Delete')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/podcasts/2', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }
}
