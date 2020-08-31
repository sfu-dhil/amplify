<?php

namespace App\Tests\Controller;

use App\Entity\Podcast;
use App\DataFixtures\PodcastFixtures;
use App\Repository\PodcastRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\Tests\ControllerBaseCase;
use Symfony\Component\HttpFoundation\Response;

class PodcastTest extends ControllerBaseCase {

    // Change this to HTTP_OK when the site is public.
    private const ANON_RESPONSE_CODE=Response::HTTP_FOUND;

    protected function fixtures() : array {
        return [
            PodcastFixtures::class,
            UserFixtures::class,
        ];
    }

    /**
     * @group anon
     * @group index
     */
    public function testAnonIndex() {
        $crawler = $this->client->request('GET', '/podcast/');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('New')->count());
    }

    /**
     * @group user
     * @group index
     */
    public function testUserIndex() {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/podcast/');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('New')->count());
    }

    /**
     * @group admin
     * @group index
     */
    public function testAdminIndex() {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/podcast/');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->selectLink('New')->count());
    }

    /**
     * @group anon
     * @group show
     */
    public function testAnonShow() {
        $crawler = $this->client->request('GET', '/podcast/1');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('Edit')->count());
    }

    /**
     * @group user
     * @group show
     */
    public function testUserShow() {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/podcast/1');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('Edit')->count());
    }

    /**
     * @group admin
     * @group show
     */
    public function testAdminShow() {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/podcast/1');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $crawler->selectLink('Edit')->count());
    }

    /**
     * @group anon
     * @group typeahead
     */
    public function testAnonTypeahead() {
        $this->client->request('GET', '/podcast/typeahead?q=podcast');
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
        $this->client->request('GET', '/podcast/typeahead?q=podcast');
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
        $this->client->request('GET', '/podcast/typeahead?q=podcast');
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertEquals(4, count($json));
    }


    public function testAnonSearch() : void {
        $crawler = $this->client->request('GET', '/podcast/search');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        if(self::ANON_RESPONSE_CODE === Response::HTTP_FOUND) {
            // If authentication is required stop here.
            return;
        }

        $repo = $this->createMock(PodcastRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('podcast.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set(PodcastRepository::class, $repo);

        $form = $crawler->selectButton('Search')->form([
            'q' => 'podcast',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New")')->count());
    }

    public function testUserSearch() : void {
        $crawler = $this->client->request('GET', '/podcast/search');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());

        $this->login('user.user');
        $repo = $this->createMock(PodcastRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('podcast.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set(PodcastRepository::class, $repo);

        $form = $crawler->selectButton('Search')->form([
            'q' => 'podcast',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New")')->count());
    }

    public function testAdminSearch() : void {
        $crawler = $this->client->request('GET', '/podcast/search');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());

        $this->login('user.admin');
        $repo = $this->createMock(PodcastRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('podcast.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set(PodcastRepository::class, $repo);

        $form = $crawler->selectButton('Search')->form([
            'q' => 'podcast',
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
        $crawler = $this->client->request('GET', '/podcast/1/edit');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group user
     * @group edit
     */
    public function testUserEdit() {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/podcast/1/edit');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group edit
     */
    public function testAdminEdit() {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/podcast/1/edit');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Save')->form([
        'podcast[title]' => 'Updated Title',
            'podcast[alternativeTitle]' => 'Updated AlternativeTitle',
            'podcast[explicit]' => 'Updated Explicit',
            'podcast[description]' => 'Updated Description',
            'podcast[copyright]' => 'Updated Copyright',
            'podcast[website]' => 'Updated Website',
            'podcast[rss]' => 'Updated Rss',
            'podcast[tags]' => 'Updated Tags',
                    ]);

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect('/podcast/1'));
        $responseCrawler = $this->client->followRedirect();
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Title")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated AlternativeTitle")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Explicit")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Description")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Copyright")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Website")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Rss")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("Updated Tags")')->count());
                }

    /**
     * @group anon
     * @group new
     */
    public function testAnonNew() {
        $crawler = $this->client->request('GET', '/podcast/new');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group anon
     * @group new
     */
    public function testAnonNewPopup() {
        $crawler = $this->client->request('GET', '/podcast/new_popup');
        $this->assertSame(Response::HTTP_FOUND, $this->client->getResponse()->getStatusCode());
        $this->assertTrue($this->client->getResponse()->isRedirect());
    }

    /**
     * @group user
     * @group new
     */
    public function testUserNew() {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/podcast/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group user
     * @group new
     */
    public function testUserNewPopup() {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/podcast/new_popup');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group new
     */
    public function testAdminNew() {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/podcast/new');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Create')->form([
        'podcast[title]' => 'New Title',
            'podcast[alternativeTitle]' => 'New AlternativeTitle',
            'podcast[explicit]' => 'New Explicit',
            'podcast[description]' => 'New Description',
            'podcast[copyright]' => 'New Copyright',
            'podcast[website]' => 'New Website',
            'podcast[rss]' => 'New Rss',
            'podcast[tags]' => 'New Tags',
                    ]);

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $responseCrawler->filter('td:contains("New Title")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New AlternativeTitle")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Explicit")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Description")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Copyright")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Website")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Rss")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Tags")')->count());
                }

    /**
     * @group admin
     * @group new
     */
    public function testAdminNewPopup() {
        $this->login('user.admin');
        $formCrawler = $this->client->request('GET', '/podcast/new_popup');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $formCrawler->selectButton('Create')->form([
        'podcast[title]' => 'New Title',
            'podcast[alternativeTitle]' => 'New AlternativeTitle',
            'podcast[explicit]' => 'New Explicit',
            'podcast[description]' => 'New Description',
            'podcast[copyright]' => 'New Copyright',
            'podcast[website]' => 'New Website',
            'podcast[rss]' => 'New Rss',
            'podcast[tags]' => 'New Tags',
                    ]);

        $this->client->submit($form);
        $this->assertTrue($this->client->getResponse()->isRedirect());
        $responseCrawler = $this->client->followRedirect();
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(1, $responseCrawler->filter('td:contains("New Title")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New AlternativeTitle")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Explicit")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Description")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Copyright")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Website")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Rss")')->count());
            $this->assertEquals(1, $responseCrawler->filter('td:contains("New Tags")')->count());
                }

    /**
     * @group admin
     * @group delete
     */
    public function testAdminDelete() {
        $repo = self::$container->get(PodcastRepository::class);
        $preCount = count($repo->findAll());
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/podcast/1');
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
