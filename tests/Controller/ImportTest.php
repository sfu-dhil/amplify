<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserExtraFixtures;
use App\Message\ImportMessage;
use App\Repository\ImportRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class ImportTest extends ControllerTestCase {
    use InteractsWithMessenger;

    public function testNew() : void {
        $repo = self::getContainer()->get(ImportRepository::class);
        $preCount = count($repo->findAll());
        $this->messenger('async')->queue()->assertEmpty();

        // Anon
        $crawler = $this->client->request('GET', '/podcasts/imports/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access, User with podcast access, Admin
        $newId = 9;
        foreach ([UserFixtures::USER, UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $formCrawler = $this->client->request('GET', '/podcasts/imports/new');
            $this->assertResponseIsSuccessful();

            $form = $formCrawler->selectButton('Import')->form([
                'import[rss]' => "https://rss.com/{$newId}",
            ]);

            $this->client->submit($form);
            $this->assertResponseRedirects("/podcasts/imports/{$newId}", Response::HTTP_FOUND);
            $responseCrawler = $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
            $newId++;
        }
        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount + 3, $postCount);

        $this->messenger('async')->queue()->assertCount(3);
        $this->messenger('async')->queue()->assertContains(ImportMessage::class, 3);
        $this->assertSame($this->messenger('async')->queue()->messages(ImportMessage::class)[0]->getImportId(), 9);
        $this->assertSame($this->messenger('async')->queue()->messages(ImportMessage::class)[1]->getImportId(), 10);
        $this->assertSame($this->messenger('async')->queue()->messages(ImportMessage::class)[2]->getImportId(), 11);
    }

    public function testPodcastNew() : void {
        $repo = self::getContainer()->get(ImportRepository::class);
        $preCount = count($repo->findAll());
        $this->messenger('async')->queue()->assertEmpty();

        // Anon
        $crawler = $this->client->request('GET', '/podcasts/1/imports/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/1/imports/new');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        //  User with podcast access, Admin
        $newId = 12;
        foreach ([UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $this->client->request('GET', '/podcasts/1/imports/new');
            $this->assertResponseRedirects("/podcasts/imports/{$newId}", Response::HTTP_FOUND);
            $responseCrawler = $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
            $newId++;
        }

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount + 2, $postCount);

        $this->messenger('async')->queue()->assertCount(2);
        $this->messenger('async')->queue()->assertContains(ImportMessage::class, 2);
        $this->assertSame($this->messenger('async')->queue()->messages(ImportMessage::class)[0]->getImportId(), 12);
        $this->assertSame($this->messenger('async')->queue()->messages(ImportMessage::class)[1]->getImportId(), 13);
    }

    public function testShow() : void {
        // Anon
        $crawler = $this->client->request('GET', '/podcasts/imports/1');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access, User with podcast access, Admin
        foreach ([UserFixtures::USER, UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/podcasts/imports/1');
            $this->assertResponseIsSuccessful();
        }
    }
}
