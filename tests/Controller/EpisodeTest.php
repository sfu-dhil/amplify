<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Tests\Controller;

use App\DataFixtures\EpisodeFixtures;
use App\Repository\EpisodeRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\Tests\ControllerBaseCase;
use Symfony\Component\HttpFoundation\Response;

class EpisodeTest extends ControllerBaseCase {
    // Change this to HTTP_OK when the site is public.
    private const ANON_RESPONSE_CODE = Response::HTTP_FOUND;

    private const TYPEAHEAD_QUERY = 'title';

    protected function fixtures() : array {
        return [
            EpisodeFixtures::class,
            UserFixtures::class,
        ];
    }

    /**
     * @group anon
     * @group index
     */
    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/episode/');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('New')->count());
    }

    /**
     * @group user
     * @group index
     */
    public function testUserIndex() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/episode/');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('New')->count());
    }

    /**
     * @group admin
     * @group index
     */
    public function testAdminIndex() : void {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/episode/');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->selectLink('New')->count());
    }

    /**
     * @group anon
     * @group show
     */
    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/episode/1');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('Edit')->count());
    }

    /**
     * @group user
     * @group show
     */
    public function testUserShow() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/episode/1');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(0, $crawler->selectLink('Edit')->count());
    }

    /**
     * @group admin
     * @group show
     */
    public function testAdminShow() : void {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/episode/1');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $crawler->selectLink('Edit')->count());
    }

    /**
     * @group anon
     * @group typeahead
     */
    public function testAnonTypeahead() : void {
        $this->client->request('GET', '/episode/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        if (self::ANON_RESPONSE_CODE === Response::HTTP_FOUND) {
            // If authentication is required stop here.
            return;
        }
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertSame(4, count($json));
    }

    /**
     * @group user
     * @group typeahead
     */
    public function testUserTypeahead() : void {
        $this->login('user.user');
        $this->client->request('GET', '/episode/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertSame(4, count($json));
    }

    /**
     * @group admin
     * @group typeahead
     */
    public function testAdminTypeahead() : void {
        $this->login('user.admin');
        $this->client->request('GET', '/episode/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertSame(4, count($json));
    }

    public function testAnonSearch() : void {
        $repo = $this->createMock(EpisodeRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('episode.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set(EpisodeRepository::class, $repo);

        $crawler = $this->client->request('GET', '/episode/search');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
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
        $repo = $this->createMock(EpisodeRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('episode.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set(EpisodeRepository::class, $repo);

        $this->login('user.user');
        $crawler = $this->client->request('GET', '/episode/search');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'episode',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminSearch() : void {
        $repo = $this->createMock(EpisodeRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('episode.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set(EpisodeRepository::class, $repo);

        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/episode/search');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'episode',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group anon
     * @group edit
     */
    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/episode/1/edit');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group user
     * @group edit
     */
    public function testUserEdit() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/episode/1/edit');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group edit
     */
    public function testAdminEdit() : void {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/episode/1/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Save')->form([
            'episode[number]' => 12,
            'episode[podcast]' => 2,
            'episode[date]' => "2020-09-09",
            'episode[runTime]' => '1234',
            'episode[title]' => 'Updated Title',
            'episode[alternativeTitle]' => 'Updated AlternativeTitle',
            'episode[bibliography]' => 'Updated Bibliography',
            'episode[copyright]' => 'Updated Copyright',
            'episode[transcript]' => 'Updated Transcript',
            'episode[abstract]' => 'Updated Abstract',
        ]);

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect('/episode/1'));
        $responseCrawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(2, $responseCrawler->filter('td:contains("12")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("1234")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("Updated Title")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("Updated AlternativeTitle")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("Updated Bibliography")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("Updated Copyright")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("Updated Transcript")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("Updated Abstract")')->count());
    }

    /**
     * @group anon
     * @group new
     */
    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/episode/new');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group anon
     * @group new
     */
    public function testAnonNewPopup() : void {
        $crawler = $this->client->request('GET', '/episode/new_popup');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group user
     * @group new
     */
    public function testUserNew() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/episode/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group user
     * @group new
     */
    public function testUserNewPopup() : void {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/episode/new_popup');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group new
     */
    public function testAdminNew() : void {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/episode/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Save')->form([
            'episode[number]' => 12,
            'episode[podcast]' => $this->getReference('podcast.1')->getId(),
            'episode[date]' => "2020-09-09",
            'episode[runTime]' => '1234',
            'episode[title]' => 'New Title',
            'episode[alternativeTitle]' => 'New AlternativeTitle',
            'episode[bibliography]' => 'New Bibliography',
            'episode[copyright]' => 'New Copyright',
            'episode[transcript]' => 'New Transcript',
            'episode[abstract]' => 'New Abstract',
        ]);

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(2, $responseCrawler->filter('td:contains("12")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("1234")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New Title")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New AlternativeTitle")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New Bibliography")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New Copyright")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New Transcript")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New Abstract")')->count());
    }

    /**
     * @group admin
     * @group new
     */
    public function testAdminNewPopup() : void {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/episode/new_popup');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Save')->form([
            'episode[number]' => 12,
            'episode[podcast]' => $this->getReference('podcast.1')->getId(),
            'episode[date]' => "2020-09-09",
            'episode[runTime]' => '1234',
            'episode[title]' => 'New Title',
            'episode[alternativeTitle]' => 'New AlternativeTitle',
            'episode[bibliography]' => 'New Bibliography',
            'episode[copyright]' => 'New Copyright',
            'episode[transcript]' => 'New Transcript',
            'episode[abstract]' => 'New Abstract',
        ]);

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertSame(2, $responseCrawler->filter('td:contains("12")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("1234")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New Title")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New AlternativeTitle")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New Bibliography")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New Copyright")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New Transcript")')->count());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New Abstract")')->count());
    }

    /**
     * @group admin
     * @group delete
     */
    public function testAdminDelete() : void {
        $repo = self::$container->get(EpisodeRepository::class);
        $preCount = count($repo->findAll());

        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/episode/1');
        $form = $crawler->selectButton('Delete')->form();
        $this->client->submit($form);

        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $this->entityManager->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }
}
