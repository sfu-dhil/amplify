<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Message\ExportMessage;
use App\Repository\ExportRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class ExportTest extends ControllerTestCase {
    use InteractsWithMessenger;

    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/podcasts/2/exports');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertSame(0, $crawler->filter('.page-actions')->selectLink('New')->count());
    }

    public function testUserIndex() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/exports');
        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->filter('.page-actions')->selectLink('New')->count());
    }

    public function testAdminIndex() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/2/exports');
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('.page-actions')->selectLink('New')->count());
    }

    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/podcasts/2/exports/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserNew() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/exports/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNew() : void {
        $repo = self::getContainer()->get(ExportRepository::class);
        $preCount = count($repo->findAll());
        $this->messenger('async')->queue()->assertEmpty();

        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/podcasts/2/exports/new');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Export')->form([
            'export[format]' => 'mods',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/2/exports/9', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount + 1, $postCount);

        $this->messenger('async')->queue()->assertCount(1);
        $this->messenger('async')->queue()->assertContains(ExportMessage::class, 1);
        $this->assertSame($this->messenger('async')->queue()->messages(ExportMessage::class)[0]->getExportId(), 9);
    }

    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/podcasts/2/exports/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testUserShow() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/exports/1');
        $this->assertResponseIsSuccessful();
    }

    public function testAdminShow() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/2/exports/1');
        $this->assertResponseIsSuccessful();
    }

    public function testAnonDownload() : void {
        $crawler = $this->client->request('GET', '/podcasts/2/exports/3/download');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserDownload() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/exports/3/download');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/zip');
    }

    public function testAdminDownload() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/2/exports/3/download');
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('Content-Type', 'application/zip');
    }

    public function testAdminDelete() : void {
        $filePath = dirname(__FILE__, 3) . '/data/test/exports/3.zip';
        $fileSystem = self::getContainer()->get(Filesystem::class);
        $repo = self::getContainer()->get(ExportRepository::class);
        $preCount = count($repo->findAll());
        $this->assertTrue($fileSystem->exists($filePath));

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/2/exports');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[action="/podcasts/2/exports/3"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/2/exports');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
        $this->assertFalse($fileSystem->exists($filePath));
    }
}
