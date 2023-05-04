<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Message\ImportMessage;
use App\Repository\ImportRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class ImportTest extends ControllerTestCase {
    use InteractsWithMessenger;

    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/podcasts/imports/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserNew() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/imports/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNew() : void {
        $repo = self::getContainer()->get(ImportRepository::class);
        $preCount = count($repo->findAll());
        $this->messenger('async')->queue()->assertEmpty();

        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/podcasts/imports/new');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Import')->form([
            'import[rss]' => 'https://rss.com/3',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/imports/9', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount + 1, $postCount);

        $this->messenger('async')->queue()->assertCount(1);
        $this->messenger('async')->queue()->assertContains(ImportMessage::class, 1);
        $this->assertSame($this->messenger('async')->queue()->messages(ImportMessage::class)[0]->getImportId(), 9);
    }

    public function testAnonPodcastNew() : void {
        $crawler = $this->client->request('GET', '/podcasts/1/imports/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserPodcastNew() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/1/imports/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminPodcastNew() : void {
        $repo = self::getContainer()->get(ImportRepository::class);
        $preCount = count($repo->findAll());
        $this->messenger('async')->queue()->assertEmpty();

        $this->login(UserFixtures::ADMIN);
        $this->client->request('GET', '/podcasts/1/imports/new');
        $this->assertResponseRedirects('/podcasts/imports/10', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount + 1, $postCount);

        $this->messenger('async')->queue()->assertCount(1);
        $this->messenger('async')->queue()->assertContains(ImportMessage::class, 1);
        $this->assertSame($this->messenger('async')->queue()->messages(ImportMessage::class)[0]->getImportId(), 10);
    }

    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/podcasts/imports/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testUserShow() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/imports/1');
        $this->assertResponseIsSuccessful();
    }

    public function testAdminShow() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/imports/1');
        $this->assertResponseIsSuccessful();
    }
}
