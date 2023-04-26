<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\EpisodeRepository;
use Nines\MediaBundle\Repository\AudioRepository;
use Nines\MediaBundle\Repository\ImageRepository;
use Nines\MediaBundle\Repository\PdfRepository;
use Nines\MediaBundle\Service\AudioManager;
use Nines\MediaBundle\Service\ImageManager;
use Nines\MediaBundle\Service\PdfManager;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class EpisodeImageTest extends ControllerTestCase {
    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/images/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserNew() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/images/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNew() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/images/new');
        $this->assertResponseIsSuccessful();

        $manager = self::getContainer()->get(ImageManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Create')->form([
            'image[description]' => 'Description',
            'image[license]' => 'License',
        ]);
        $form['image[file]']->upload(dirname(__FILE__, 2) . '/data/image/28213926366_4430448ff7_c.jpg');
        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/2/episodes/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/images/22/edit');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserEdit() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/images/22/edit');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEdit() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/images/22/edit');
        $this->assertResponseIsSuccessful();

        $manager = self::getContainer()->get(ImageManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Update')->form([
            'image[description]' => 'Updated Description',
            'image[license]' => 'Updated License',
        ]);
        $form['image[newFile]']->upload(dirname(__FILE__, 2) . '/data/image/3632486652_b432f7b283_c.jpg');
        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/2/episodes/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonDelete() : void {
        $crawler = $this->client->request('DELETE', '/podcasts/2/episodes/4/images/25');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserDelete() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('DELETE', '/podcasts/2/episodes/4/images/25');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminDelete() : void {
        $repo = self::getContainer()->get(ImageRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[action="/podcasts/2/episodes/4/images/25"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/2/episodes/4');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }
}