<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserExtraFixtures;
use App\Repository\PersonRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class PersonTest extends ControllerTestCase {
    private const SEARCH_QUERY = 'fullname';

    public function testIndex() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/people');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access,
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/people');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/podcasts/2/people');
            $this->assertResponseIsSuccessful();
        }
    }

    public function testTypeahead() : void {
        // Anon
        $this->client->request('GET', '/podcasts/2/people/typeahead?q=' . self::SEARCH_QUERY);
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access,
        $this->login(UserFixtures::USER);
        $this->client->request('GET', '/podcasts/2/people/typeahead?q=' . self::SEARCH_QUERY);
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $this->client->request('GET', '/podcasts/2/people/typeahead?q=' . self::SEARCH_QUERY);
            $response = $this->client->getResponse();
            $this->assertResponseIsSuccessful();
            $this->assertSame('application/json', $response->headers->get('content-type'));
            $json = json_decode($response->getContent());
            $this->assertCount(4, $json);
        }
    }

    public function testSearch() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/people');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access,
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/people');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/podcasts/2/people');
            $this->assertResponseIsSuccessful();

            $form = $crawler->selectButton('btn-search')->form([
                'q' => 'person',
            ]);

            $responseCrawler = $this->client->submit($form);
            $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        }
    }

    public function testEdit() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/people/5/edit');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access,
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/people/5/edit');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // User without podcast access, User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $formCrawler = $this->client->request('GET', '/podcasts/2/people/5/edit');
            $this->assertResponseIsSuccessful();

            $form = $formCrawler->selectButton('Save')->form([
                'person[fullname]' => 'Updated Fullname',
                'person[sortableName]' => 'Updated SortableName',
                'person[location]' => 'Updated Location',
                'person[bio]' => '<p>Updated Text</p>',
                'person[institution]' => 'Updated Institution',
            ]);

            $this->client->submit($form);
            $this->assertResponseRedirects('/podcasts/2/people', Response::HTTP_FOUND);
            $responseCrawler = $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
        }
    }

    public function testNew() : void {
        $repo = self::getContainer()->get(PersonRepository::class);
        $newId = count($repo->findAll()) + 1;

        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/people/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access,
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/people/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // User without podcast access, User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $formCrawler = $this->client->request('GET', '/podcasts/2/people/new');
            $this->assertResponseIsSuccessful();

            $form = $formCrawler->selectButton('Save')->form([
                'person[fullname]' => "Updated Fullname {$newId}",
                'person[sortableName]' => 'Updated SortableName',
                'person[location]' => 'Updated Location',
                'person[bio]' => '<p>Updated Text</p>',
                'person[institution]' => 'Updated Institution',
            ]);

            $this->client->submit($form);
            $response = $this->client->getResponse();
            $this->assertResponseIsSuccessful();
            $this->assertSame('application/json', $response->headers->get('content-type'));
            $json = (array) json_decode($response->getContent());
            $this->assertSame($json, [
                'success' => true,
            ]);
            $newId++;
        }
    }

    public function testDelete() : void {
        $repo = self::getContainer()->get(PersonRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserExtraFixtures::USER_WITH_ACCESS);
        $crawler = $this->client->request('GET', '/podcasts/2/people');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[action="/podcasts/2/people/8"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/2/people', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }
}
