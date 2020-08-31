<?php

namespace App\Tests\Controller;

use App\Entity\Audio;
use App\DataFixtures\AudioFixtures;
use App\Repository\AudioRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\Tests\ControllerBaseCase;
use Symfony\Component\HttpFoundation\Response;

class AudioTest extends ControllerBaseCase {

    // Change this to HTTP_OK when the site is public.
    private const ANON_RESPONSE_CODE=Response::HTTP_FOUND;

    protected function fixtures() : array {
        return [
            AudioFixtures::class,
            UserFixtures::class,
        ];
    }

    /**
     * @group anon
     * @group index
     */
    public function testAnonIndex() {
        $crawler = $this->client->request('GET', '/audio/');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(0, $crawler->selectLink('New')->count());
    }

    /**
     * @group user
     * @group index
     */
    public function testUserIndex() {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/audio/');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group index
     */
    public function testAdminIndex() {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/audio/');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group anon
     * @group show
     */
    public function testAnonShow() {
        $crawler = $this->client->request('GET', '/audio/1');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group user
     * @group show
     */
    public function testUserShow() {
        $this->login('user.user');
        $crawler = $this->client->request('GET', '/audio/1');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @group admin
     * @group show
     */
    public function testAdminShow() {
        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/audio/1');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testAnonSearch() : void {
        $crawler = $this->client->request('GET', '/audio/search');
        $this->assertSame(self::ANON_RESPONSE_CODE, $this->client->getResponse()->getStatusCode());
        if(self::ANON_RESPONSE_CODE === Response::HTTP_FOUND) {
            // If authentication is required stop here.
            return;
        }

        $repo = $this->createMock(AudioRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('audio.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set(AudioRepository::class, $repo);

        $form = $crawler->selectButton('Search')->form([
            'q' => 'audio',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(1, $responseCrawler->filter('td:contains("New")')->count());
    }

    public function testUserSearch() : void {
        $repo = $this->createMock(AudioRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('audio.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set(AudioRepository::class, $repo);

        $this->login('user.user');
        $crawler = $this->client->request('GET', '/audio/search');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Search')->form([
            'q' => 'audio',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(2, $responseCrawler->filter('td:contains("New")')->count());
    }

    public function testAdminSearch() : void {
        $repo = $this->createMock(AudioRepository::class);
        $repo->method('searchQuery')->willReturn([$this->getReference('audio.1')]);
        $this->client->disableReboot();
        $this->client->getContainer()->set(AudioRepository::class, $repo);

        $this->login('user.admin');
        $crawler = $this->client->request('GET', '/audio/search');
        $this->assertSame(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $form = $crawler->selectButton('Search')->form([
            'q' => 'audio',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        $this->assertSame(2, $responseCrawler->filter('td:contains("New")')->count());
    }
}
