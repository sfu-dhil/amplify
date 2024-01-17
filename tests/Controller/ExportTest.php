<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserExtraFixtures;
use App\Message\ExportMessage;
use App\Repository\ExportRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class ExportTest extends ControllerTestCase {
    use InteractsWithMessenger;

    public function testIndex() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/exports');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/exports');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/podcasts/2/exports');
            $this->assertResponseIsSuccessful();
            $this->assertSame(1, $crawler->filter('.page-actions')->selectLink('New')->count());
        }
    }

    public function testNew() : void {
        $repo = self::getContainer()->get(ExportRepository::class);
        $preCount = count($repo->findAll());
        $this->messenger('async')->queue()->assertEmpty();

        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/exports/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/exports/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        $newId = 9;
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $formCrawler = $this->client->request('GET', '/podcasts/2/exports/new');
            $this->assertResponseIsSuccessful();

            $form = $formCrawler->selectButton('Export')->form([
                'export[format]' => 'mods',
            ]);

            $this->client->submit($form);
            $this->assertResponseRedirects("/podcasts/2/exports/{$newId}", Response::HTTP_FOUND);
            $responseCrawler = $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
            $newId++;
        }

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount + 2, $postCount);

        $this->messenger('async')->queue()->assertCount(2);
        $this->messenger('async')->queue()->assertContains(ExportMessage::class, 2);
        $this->assertSame($this->messenger('async')->queue()->messages(ExportMessage::class)[0]->getExportId(), 9);
        $this->assertSame($this->messenger('async')->queue()->messages(ExportMessage::class)[1]->getExportId(), 10);
    }

    public function testShow() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/exports/1');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/exports/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/podcasts/2/exports/1');
            $this->assertResponseIsSuccessful();
        }
    }

    public function testDownload() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/2/exports/3/download');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/exports/3/download');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/podcasts/2/exports/3/download');
            $this->assertResponseIsSuccessful();
            $this->assertResponseHeaderSame('Content-Type', 'application/zip');
        }
    }

    public function testDelete() : void {
        $filePath = dirname(__FILE__, 3) . '/data/test/exports/3.zip';
        $fileSystem = self::getContainer()->get(Filesystem::class);
        $repo = self::getContainer()->get(ExportRepository::class);
        $preCount = count($repo->findAll());
        $this->assertTrue($fileSystem->exists($filePath));

        $this->login(UserExtraFixtures::USER_WITH_ACCESS);
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
