<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserExtraFixtures;
use App\Repository\ShareRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class ShareTest extends ControllerTestCase {
    private const SEARCH_QUERY = 'user';

    public function testIndex() : void {
        // Anon
        $this->client->request('GET', '/podcasts/2/shares');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $this->client->request('GET', '/podcasts/2/shares');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // User without podcast access, User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/podcasts/2/shares');
            $this->assertResponseIsSuccessful();
            $this->assertEquals(1, $crawler->filter('form')->selectButton('Share')->count());
        }
    }

    public function testTypeahead() : void {
        // Anon
        $this->client->request('GET', '/podcasts/2/shares/typeahead?q=' . self::SEARCH_QUERY);
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $this->client->request('GET', '/podcasts/2/shares/typeahead?q=' . self::SEARCH_QUERY);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $this->client->request('GET', '/podcasts/2/shares/typeahead?q=' . self::SEARCH_QUERY);
            $response = $this->client->getResponse();
            $this->assertResponseIsSuccessful();
            $this->assertEquals('application/json', $response->headers->get('content-type'));
            $json = json_decode($response->getContent());
            $this->assertCount(4, $json);
        }
    }

    public function testNew() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/shares');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/shares');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $formCrawler = $this->client->request('GET', '/podcasts/2/shares');
            $this->assertResponseIsSuccessful();

            $form = $formCrawler->selectButton('Share')->form();
            $this->overrideField($form, 'share[user]', '1');

            $this->client->submit($form);
            $this->assertResponseRedirects('/podcasts/2/shares', Response::HTTP_FOUND);
            $responseCrawler = $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
        }
    }

    public function testDelete() : void {
        $repo = self::getContainer()->get(ShareRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/3/shares');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[action="/podcasts/3/shares/3"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/3/shares');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertEquals($preCount - 1, $postCount);
    }
}
