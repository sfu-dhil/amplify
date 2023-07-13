<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\DataFixtures\UserExtraFixtures;
use App\Repository\ContributorRoleRepository;
use Nines\UserBundle\DataFixtures\UserFixtures;
use Nines\UtilBundle\TestCase\ControllerTestCase;
use Symfony\Component\HttpFoundation\Response;

class ContributorRoleTest extends ControllerTestCase {
    private const SEARCH_QUERY = 'label';

    public function testIndex() : void {
        // Anon
        $crawler = $this->client->request('GET', '/contributor_roles');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access, User with podcast access, Admin
        foreach ([UserFixtures::USER, UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/contributor_roles');
            $this->assertResponseIsSuccessful();
            $this->assertSame(1, $crawler->filter('.page-actions')->selectLink('New')->count());
        }
    }

    public function testShow() : void {
        // Anon
        $crawler = $this->client->request('GET', '/contributor_roles/1');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access, User with podcast access, Admin
        foreach ([UserFixtures::USER, UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/contributor_roles/1');
            $this->assertResponseIsSuccessful();
            $this->assertSame(1, $crawler->filter('.page-actions')->selectLink('Edit')->count());
        }
    }

    public function testTypeahead() : void {
        // Anon
        $this->client->request('GET', '/contributor_roles/typeahead?q=' . self::SEARCH_QUERY);
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access, User with podcast access, Admin
        foreach ([UserFixtures::USER, UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $this->client->request('GET', '/contributor_roles/typeahead?q=' . self::SEARCH_QUERY);
            $response = $this->client->getResponse();
            $this->assertResponseIsSuccessful();
            $this->assertSame('application/json', $response->headers->get('content-type'));
            $json = json_decode($response->getContent());
            $this->assertCount(4, $json);
        }
    }

    public function testSearch() : void {
        // Anon
        $crawler = $this->client->request('GET', '/contributor_roles');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access, User with podcast access, Admin
        foreach ([UserFixtures::USER, UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $crawler = $this->client->request('GET', '/contributor_roles');
            $this->assertResponseIsSuccessful();

            $form = $crawler->selectButton('btn-search')->form([
                'q' => 'contributorRole',
            ]);

            $responseCrawler = $this->client->submit($form);
            $this->assertSame(200, $this->client->getResponse()->getStatusCode());
        }
    }

    public function testEdit() : void {
        // Anon
        $crawler = $this->client->request('GET', '/contributor_roles/1/edit');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access, User with podcast access, Admin
        foreach ([UserFixtures::USER, UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
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
    }

    public function testNew() : void {
        // Anon
        $crawler = $this->client->request('GET', '/contributor_roles/new');
        $this->assertResponseRedirects('http://localhost/login', Response::HTTP_FOUND);

        // User without podcast access, User with podcast access, Admin
        $newId = 5;
        foreach ([UserFixtures::USER, UserExtraFixtures::USER_WITH_ACCESS, UserFixtures::ADMIN] as $loginCredentials) {
            $this->login($loginCredentials);
            $formCrawler = $this->client->request('GET', '/contributor_roles/new');
            $this->assertResponseIsSuccessful();

            $form = $formCrawler->selectButton('Create')->form([
                'contributor_role[label]' => "Updated Label{$newId}",
                'contributor_role[description]' => "<p>Updated Text{$newId}</p>",
                'contributor_role[relatorTerm]' => "abc{$newId}",
            ]);

            $this->client->submit($form);
            $this->assertResponseRedirects("/contributor_roles/{$newId}", Response::HTTP_FOUND);
            $responseCrawler = $this->client->followRedirect();
            $this->assertResponseIsSuccessful();
            $newId++;
        }
    }

    public function testDelete() : void {
        $repo = self::getContainer()->get(ContributorRoleRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::USER);
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
