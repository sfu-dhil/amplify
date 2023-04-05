<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Repository\ContributorRoleRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class ContributorRoleTest extends ControllerTestCase {
    private const SEARCH_QUERY = 'label';

    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/contributor_roles');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
        $this->assertSame(0, $crawler->filter('.page-actions')->selectLink('New')->count());
    }

    public function testUserIndex() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/contributor_roles');
        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->filter('.page-actions')->selectLink('New')->count());
    }

    public function testAdminIndex() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/contributor_roles');
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('.page-actions')->selectLink('New')->count());
    }

    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/contributor_roles/1');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testUserShow() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/contributor_roles/1');
        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->filter('.page-actions')->selectLink('Edit')->count());
    }

    public function testAdminShow() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/contributor_roles/1');
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->filter('.page-actions')->selectLink('Edit')->count());
    }

    public function testAnonTypeahead() : void {
        $this->client->request('GET', '/contributor_roles/typeahead?q=' . self::SEARCH_QUERY);
        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testUserTypeahead() : void {
        $this->login(UserFixtures::USER);
        $this->client->request('GET', '/contributor_roles/typeahead?q=' . self::SEARCH_QUERY);
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertCount(4, $json);
    }

    public function testAdminTypeahead() : void {
        $this->login(UserFixtures::ADMIN);
        $this->client->request('GET', '/contributor_roles/typeahead?q=' . self::SEARCH_QUERY);
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertCount(4, $json);
    }

    public function testAnonSearch() : void {
        $crawler = $this->client->request('GET', '/contributor_roles');
        $this->assertResponseStatusCodeSame(Response::HTTP_FOUND);
    }

    public function testUserSearch() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/contributor_roles');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'contributorRole',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminSearch() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/contributor_roles');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'contributorRole',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/contributor_roles/1/edit');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserEdit() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/contributor_roles/1/edit');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEdit() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/contributor_roles/1/edit');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Update')->form([
            'contributor_role[label]' => 'Updated Label',
            'contributor_role[description]' => '<p>Updated Text</p>',
            'contributor_role[relatorTerm]' => 'abc',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/contributor_roles/1', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/contributor_roles/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);
    }

    public function testUserNew() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/contributor_roles/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNew() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/contributor_roles/new');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Create')->form([
            'contributor_role[label]' => 'Updated Label',
            'contributor_role[description]' => '<p>Updated Text</p>',
            'contributor_role[relatorTerm]' => 'abc',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/contributor_roles/5', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAdminDelete() : void {
        $repo = self::getContainer()->get(ContributorRoleRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/contributor_roles/1');
        $this->assertResponseIsSuccessful();
        $form = $crawler->selectButton('Delete')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/contributor_roles', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }
}
