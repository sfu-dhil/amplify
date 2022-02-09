<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Controller;

use App\Repository\EpisodeRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class EpisodeTest extends ControllerTestCase {
    // Change this to HTTP_OK when the site is public.
    private const ANON_RESPONSE_CODE = Response::HTTP_FOUND;

    private const TYPEAHEAD_QUERY = 'title';

    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/episode/');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
        $this->assertSame(0, $crawler->selectLink('New')->count());
    }

    public function testUserIndex() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/');
        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->selectLink('New')->count());
    }

    public function testAdminIndex() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/');
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->selectLink('New')->count());
    }

    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/episode/1');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
        $this->assertSame(0, $crawler->selectLink('Edit')->count());
    }

    public function testUserShow() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/1');
        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->selectLink('Edit')->count());
    }

    public function testAdminShow() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/1');
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->selectLink('Edit')->count());
    }

    public function testAnonTypeahead() : void {
        $this->client->request('GET', '/episode/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
        if (self::ANON_RESPONSE_CODE === Response::HTTP_FOUND) {
            // If authentication is required stop here.
            return;
        }
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertCount(4, $json);
    }

    public function testUserTypeahead() : void {
        $this->login(UserFixtures::USER);
        $this->client->request('GET', '/episode/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertCount(4, $json);
    }

    public function testAdminTypeahead() : void {
        $this->login(UserFixtures::ADMIN);
        $this->client->request('GET', '/episode/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertCount(4, $json);
    }

    public function testAnonSearch() : void {
        $crawler = $this->client->request('GET', '/episode/search');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
        if (self::ANON_RESPONSE_CODE === Response::HTTP_FOUND) {
            // If authentication is required stop here.
            return;
        }

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'episode',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserSearch() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/search');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'episode',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminSearch() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/search');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'episode',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/episode/1/edit');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEdit() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/1/edit');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEdit() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/episode/1/edit');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Save')->form([
            'episode[number]' => 10,
            'episode[date]' => '2020-01-01',
            'episode[runTime]' => '00:09:20',
            'episode[title]' => 'Updated Title',
            'episode[alternativeTitle]' => 'Updated AlternativeTitle',
            'episode[bibliography]' => '<p>Updated Text</p>',
            'episode[copyright]' => '<p>Updated Text</p>',
            'episode[transcript]' => '<p>Updated Text</p>',
            'episode[abstract]' => '<p>Updated Text</p>',
        ]);
        $form['episode[season]']->disableValidation()->setValue(2);
        $form['episode[podcast]']->disableValidation()->setValue(2);

        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/1', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/episode/new');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testAnonNewPopup() : void {
        $crawler = $this->client->request('GET', '/episode/new_popup');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserNew() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testUserNewPopup() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/new_popup');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNew() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/episode/new');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Save')->form([
            'episode[number]' => 10,
            'episode[date]' => '2020-01-01',
            'episode[runTime]' => '00:09:20',
            'episode[title]' => 'Updated Title',
            'episode[alternativeTitle]' => 'Updated AlternativeTitle',
            'episode[bibliography]' => '<p>Updated Text</p>',
            'episode[copyright]' => '<p>Updated Text</p>',
            'episode[transcript]' => '<p>Updated Text</p>',
            'episode[abstract]' => '<p>Updated Text</p>',
        ]);
        $form['episode[season]']->disableValidation()->setValue(2);
        $form['episode[podcast]']->disableValidation()->setValue(2);

        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/5', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAdminNewPopup() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/episode/new');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Save')->form([
            'episode[number]' => 10,
            'episode[date]' => '2020-01-01',
            'episode[runTime]' => '00:09:20',
            'episode[title]' => 'Updated Title',
            'episode[alternativeTitle]' => 'Updated AlternativeTitle',
            'episode[bibliography]' => '<p>Updated Text</p>',
            'episode[copyright]' => '<p>Updated Text</p>',
            'episode[transcript]' => '<p>Updated Text</p>',
            'episode[abstract]' => '<p>Updated Text</p>',
        ]);
        $form['episode[season]']->disableValidation()->setValue(2);
        $form['episode[podcast]']->disableValidation()->setValue(2);

        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/6', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAdminDelete() : void {
        /** @var EpisodeRepository $repo */
        $repo = self::$container->get(EpisodeRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/1');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Delete')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/episode/', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }
}
