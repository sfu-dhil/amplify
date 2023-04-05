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

class EpisodeAudioTest extends ControllerTestCase {
    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/audio/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserNew() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/audio/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNew() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/audio/new');
        $this->assertResponseIsSuccessful();

        $manager = self::getContainer()->get(AudioManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Create')->form([
            'audio[description]' => 'Description',
            'audio[license]' => 'License',
        ]);
        $form['audio[file]']->upload(dirname(__FILE__, 2) . '/data/audio/443027__pramonette__thunder-long.mp3');
        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/2/episodes/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/audio/1/edit');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserEdit() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/audio/6/edit');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEdit() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/audio/6/edit');
        $this->assertResponseIsSuccessful();

        $manager = self::getContainer()->get(AudioManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Update')->form([
            'audio[description]' => 'Updated Description',
            'audio[license]' => 'Updated License',
        ]);
        $form['audio[newFile]']->upload(dirname(__FILE__, 2) . '/data/audio/443027__pramonette__thunder-long.mp3');
        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/2/episodes/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonDelete() : void {
        $crawler = $this->client->request('DELETE', '/podcasts/2/episodes/4/audio/9');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserDelete() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('DELETE', '/podcasts/2/episodes/4/audio/9');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminDelete() : void {
        $repo = self::getContainer()->get(AudioRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form[action="/podcasts/2/episodes/4/audio/9"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/2/episodes/4');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }
}
