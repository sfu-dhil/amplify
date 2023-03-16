<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Message\ExportMessage;
use App\Repository\ExportRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;
use Zenstruck\Messenger\Test\InteractsWithMessenger;

class ExportTest extends ControllerTestCase {
    use InteractsWithMessenger;

    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/season/3/export/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserNew() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/season/3/export/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNew() : void {
        /** @var ExportRepository $repo */
        $repo = self::getContainer()->get(ExportRepository::class);
        $preCount = count($repo->findAll());
        $this->messenger('async')->queue()->assertEmpty();

        $this->login(UserFixtures::ADMIN);
        $this->client->request('GET', '/season/3/export/new');
        $this->assertResponseRedirects('/season/3', Response::HTTP_FOUND);

        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount + 1, $postCount);

        $this->messenger('async')->queue()->assertCount(1);
        $this->messenger('async')->queue()->assertContains(ExportMessage::class, 1);
        $this->assertSame($this->messenger('async')->queue()->messages(ExportMessage::class)[0]->getExportId(), $postCount);
    }

    public function testAdminDelete() : void {
        /** @var ExportRepository $repo */
        $repo = self::getContainer()->get(ExportRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/season/3');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('.delete-form.delete-export-3')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/season/3', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }
}
