<?php

namespace App\Tests\Controller;

use App\Entity\Episode;
use App\DataFixtures\EpisodeFixtures;
use App\Repository\EpisodeRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\Tests\ControllerBaseCase;
use Symfony\Component\HttpFoundation\Response;

class EpisodeTest extends ControllerBaseCase {

    // Change this to HTTP_OK when the site is public.
    private const ANON_RESPONSE_CODE=Response::HTTP_FOUND;

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
    public function testAnonIndex() {
        $crawler = $this->client->request('GET', '/episode/');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('New')->count());
    }

    /**
     * @group user
     * @group index
     */
    public function testUserIndex() {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/episode/');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('New')->count());
    }

    /**
     * @group admin
     * @group index
     */
    public function testAdminIndex() {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/episode/');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->selectLink('New')->count());
    }

    /**
     * @group anon
     * @group show
     */
    public function testAnonShow() {
        $crawler = $this->client->request('GET', '/episode/1');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('Edit')->count());
    }

    /**
     * @group user
     * @group show
     */
    public function testUserShow() {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/episode/1');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('Edit')->count());
    }

    /**
     * @group admin
     * @group show
     */
    public function testAdminShow() {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/episode/1');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->selectLink('Edit')->count());
    }

    /**
     * @group anon
     * @group typeahead
     */
    public function testAnonTypeahead() {
        $this->client->request('GET', '/episode/typeahead?q=episode');
        $response = $this->client->getResponse();
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        if(self::ANON_RESPONSE_CODE === Response::HTTP_FOUND) {
            // If authentication is required stop here.
            return;
        }
        $this->assertEquals('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertEquals(4, count($json));
    }

    /**
     * @group user
     * @group typeahead
     */
    public function testUserTypeahead() {
        $this->login('user.user');
        $this->client->request('GET', '/episode/typeahead?q=episode');
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertEquals(4, count($json));
    }

    /**
     * @group admin
     * @group typeahead
     */
    public function testAdminTypeahead() {
        $this->login('user.admin');
        $this->client->request('GET', '/episode/typeahead?q=episode');
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertEquals(4, count($json));
    }


    public function testAnonSearch() : void {
        $crawler = $this->client->request('GET', '/episode/search');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        if(self::ANON_RESPONSE_CODE === Response::HTTP_FOUND) {
            // If authentication is required stop here.
            return;
        }

        $repo = $this->createMock(EpisodeRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('episode.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set(EpisodeRepository::class, $repo);

        $form = $crawler->selectButton('Search')->form([
            'q' => 'episode',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New")')->count());
    }

    public function testUserSearch() : void {
        $crawler = $this->client->request('GET', '/episode/search');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());

        $this->login('user.user');
        $repo = $this->createMock(EpisodeRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('episode.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set(EpisodeRepository::class, $repo);

        $form = $crawler->selectButton('Search')->form([
            'q' => 'episode',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New")')->count());
    }

    public function testAdminSearch() : void {
        $crawler = $this->client->request('GET', '/episode/search');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());

        $this->login('user.admin');
        $repo = $this->createMock(EpisodeRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('episode.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set(EpisodeRepository::class, $repo);

        $form = $crawler->selectButton('Search')->form([
            'q' => 'episode',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New")')->count());
    }

    /**
     * @group anon
     * @group edit
     */
    public function testAnonEdit() {
        $crawler = $this->client->request('GET', '/episode/1/edit');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group user
     * @group edit
     */
    public function testUserEdit() {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/episode/1/edit');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group edit
     */
    public function testAdminEdit() {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/episode/1/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Save')->form([
        'episode[number]' => 'Updated Number',
            'episode[date]' => 'Updated Date',
            'episode[runTime]' => 'Updated RunTime',
            'episode[title]' => 'Updated Title',
            'episode[alternativeTitle]' => 'Updated AlternativeTitle',
            'episode[language]' => 'Updated Language',
            'episode[tags]' => 'Updated Tags',
            'episode[references]' => 'Updated References',
            'episode[copyright]' => 'Updated Copyright',
            'episode[transcript]' => 'Updated Transcript',
            'episode[abstract]' => 'Updated Abstract',
                    ]);

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect('/episode/1'));
        $responseCrawler = $this->client->followRedirect();
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Number")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Date")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated RunTime")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Title")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated AlternativeTitle")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Language")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Tags")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated References")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Copyright")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Transcript")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Abstract")')->count());
                }

    /**
     * @group anon
     * @group new
     */
    public function testAnonNew() {
        $crawler = $this->client->request('GET', '/episode/new');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group anon
     * @group new
     */
    public function testAnonNewPopup() {
        $crawler = $this->client->request('GET', '/episode/new_popup');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group user
     * @group new
     */
    public function testUserNew() {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/episode/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group user
     * @group new
     */
    public function testUserNewPopup() {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/episode/new_popup');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group new
     */
    public function testAdminNew() {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/episode/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Create')->form([
        'episode[number]' => 'New Number',
            'episode[date]' => 'New Date',
            'episode[runTime]' => 'New RunTime',
            'episode[title]' => 'New Title',
            'episode[alternativeTitle]' => 'New AlternativeTitle',
            'episode[language]' => 'New Language',
            'episode[tags]' => 'New Tags',
            'episode[references]' => 'New References',
            'episode[copyright]' => 'New Copyright',
            'episode[transcript]' => 'New Transcript',
            'episode[abstract]' => 'New Abstract',
                    ]);

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $responseCrawler->filter('td:contains("New Number")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Date")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New RunTime")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Title")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New AlternativeTitle")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Language")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Tags")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New References")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Copyright")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Transcript")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Abstract")')->count());
                }

    /**
     * @group admin
     * @group new
     */
    public function testAdminNewPopup() {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/episode/new_popup');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Create')->form([
        'episode[number]' => 'New Number',
            'episode[date]' => 'New Date',
            'episode[runTime]' => 'New RunTime',
            'episode[title]' => 'New Title',
            'episode[alternativeTitle]' => 'New AlternativeTitle',
            'episode[language]' => 'New Language',
            'episode[tags]' => 'New Tags',
            'episode[references]' => 'New References',
            'episode[copyright]' => 'New Copyright',
            'episode[transcript]' => 'New Transcript',
            'episode[abstract]' => 'New Abstract',
                    ]);

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $responseCrawler->filter('td:contains("New Number")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Date")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New RunTime")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Title")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New AlternativeTitle")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Language")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Tags")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New References")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Copyright")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Transcript")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Abstract")')->count());
                }

    /**
     * @group admin
     * @group delete
     */
    public function testAdminDelete() {
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
        $this->assertEquals($preCount - 1, $postCount);
    }
}
