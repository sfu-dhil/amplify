<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\EpisodeRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class EpisodeTest extends ControllerTestCase {
    private const SEARCH_QUERY = 'title';

    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertSame(0, $crawler->filter('.page-actions')->selectLink('Edit')->count());
    }

    public function testUserShow() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1');
        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->filter('.page-actions')->selectLink('Edit')->count());
    }

    public function testAdminShow() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1');
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('.page-actions')->selectLink('Edit')->count());
    }

    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/edit');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserEdit() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/1/edit');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEdit() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/podcasts/2/episodes/1/edit');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Update')->form([
            'episode[number]' => 10,
            'episode[date]' => '2020-01-01',
            'episode[runTime]' => '00:09:20',
            'episode[title]' => 'Updated Title',
            'episode[subTitle]' => 'Updated subTitle',
            'episode[bibliography]' => '<p>Updated Text</p>',
            'episode[transcript]' => '<p>Updated Text</p>',
            'episode[description]' => '<p>Updated Text</p>',
            'episode[permissions]' => '<p>Updated Text</p>',
        ]);
        $this->overrideField($form, 'episode[season]', '2');

        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/2/episodes/1', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserNew() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNew() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/podcasts/2/episodes/new');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Create')->form([
            'episode[number]' => 10,
            'episode[date]' => '2020-01-01',
            'episode[runTime]' => '00:09:20',
            'episode[title]' => 'Updated Title',
            'episode[subTitle]' => 'Updated subTitle',
            'episode[bibliography]' => '<p>Updated Text</p>',
            'episode[transcript]' => '<p>Updated Text</p>',
            'episode[description]' => '<p>Updated Text</p>',
            'episode[permissions]' => '<p>Updated Text</p>',
        ]);
        $this->overrideField($form, 'episode[season]', '2');

        $this->client->submit($form);
        $this->assertResponseRedirects('/podcasts/2', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAdminDelete() : void {
        $repo = self::getContainer()->get(EpisodeRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/podcasts/2/episodes/3');
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form[action="/podcasts/2/episodes/3"]')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/podcasts/2', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }
}
