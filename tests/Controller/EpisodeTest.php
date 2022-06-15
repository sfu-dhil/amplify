<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

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

class EpisodeTest extends ControllerTestCase {
    // Change this to HTTP_OK when the site is public.
    private const ANON_RESPONSE_CODE = Response::HTTP_FOUND;

    private const TYPEAHEAD_QUERY = 'title';

    public function testAnonIndex() : void {
        $crawler = $this->client->request('GET', '/episode/');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
        $this->assertSame(0, $crawler->selectLink('New')->count());
    }

    public function testUserIndex() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/');
        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->selectLink('New')->count());
    }

    public function testAdminIndex() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/');
        $this->assertResponseIsSuccessful();
        $this->assertSame(1, $crawler->selectLink('New')->count());
    }

    public function testAnonShow() : void {
        $crawler = $this->client->request('GET', '/episode/1');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
        $this->assertSame(0, $crawler->selectLink('Edit')->count());
    }

    public function testUserShow() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/1');
        $this->assertResponseIsSuccessful();
        $this->assertSame(0, $crawler->selectLink('Edit')->count());
    }

    public function testAdminShow() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/1');
        $this->assertResponseIsSuccessful();
        $this->assertSame(2, $crawler->selectLink('Edit')->count());
    }

    public function testAnonTypeahead() : void {
        $this->client->request('GET', '/episode/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
        if (self::ANON_RESPONSE_CODE === Response::HTTP_FOUND) {
            // If authentication is required stop here.
            return;
        }
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertCount(4, $json);
    }

    public function testUserTypeahead() : void {
        $this->login(UserFixtures::USER);
        $this->client->request('GET', '/episode/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertCount(4, $json);
    }

    public function testAdminTypeahead() : void {
        $this->login(UserFixtures::ADMIN);
        $this->client->request('GET', '/episode/typeahead?q=' . self::TYPEAHEAD_QUERY);
        $response = $this->client->getResponse();
        $this->assertResponseIsSuccessful();
        $this->assertSame('application/json', $response->headers->get('content-type'));
        $json = json_decode($response->getContent());
        $this->assertCount(4, $json);
    }

    public function testAnonSearch() : void {
        $crawler = $this->client->request('GET', '/episode/search');
        $this->assertResponseStatusCodeSame(self::ANON_RESPONSE_CODE);
        if (self::ANON_RESPONSE_CODE === Response::HTTP_FOUND) {
            // If authentication is required stop here.
            return;
        }

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'episode',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testUserSearch() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/search');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'episode',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminSearch() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/search');
        $this->assertResponseIsSuccessful();

        $form = $crawler->selectButton('btn-search')->form([
            'q' => 'episode',
        ]);

        $responseCrawler = $this->client->submit($form);
        $this->assertSame(200, $this->client->getResponse()->getStatusCode());
    }

    public function testAnonEdit() : void {
        $crawler = $this->client->request('GET', '/episode/1/edit');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEdit() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/1/edit');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEdit() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/episode/1/edit');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Save')->form([
            'episode[number]' => 10,
            'episode[date]' => '2020-01-01',
            'episode[runTime]' => '00:09:20',
            'episode[title]' => 'Updated Title',
            'episode[subTitle]' => 'Updated subTitle',
            'episode[bibliography]' => '<p>Updated Text</p>',
            'episode[copyright]' => '<p>Updated Text</p>',
            'episode[transcript]' => '<p>Updated Text</p>',
            'episode[abstract]' => '<p>Updated Text</p>',
        ]);
        $this->overrideField($form, 'episode[season]', 2);
        $this->overrideField($form, 'episode[podcast]', 2);

        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/1', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAnonNew() : void {
        $crawler = $this->client->request('GET', '/episode/new');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testAnonNewPopup() : void {
        $crawler = $this->client->request('GET', '/episode/new_popup');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserNew() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/new');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testUserNewPopup() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/new_popup');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNew() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/episode/new');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Save')->form([
            'episode[number]' => 10,
            'episode[date]' => '2020-01-01',
            'episode[runTime]' => '00:09:20',
            'episode[title]' => 'Updated Title',
            'episode[subTitle]' => 'Updated subTitle',
            'episode[bibliography]' => '<p>Updated Text</p>',
            'episode[copyright]' => '<p>Updated Text</p>',
            'episode[transcript]' => '<p>Updated Text</p>',
            'episode[abstract]' => '<p>Updated Text</p>',
        ]);
        $this->overrideField($form, 'episode[season]', 2);
        $this->overrideField($form, 'episode[podcast]', 2);

        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/5', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAdminNewPopup() : void {
        $this->login(UserFixtures::ADMIN);
        $formCrawler = $this->client->request('GET', '/episode/new');
        $this->assertResponseIsSuccessful();

        $form = $formCrawler->selectButton('Save')->form([
            'episode[number]' => 10,
            'episode[date]' => '2020-01-01',
            'episode[runTime]' => '00:09:20',
            'episode[title]' => 'Updated Title',
            'episode[subTitle]' => 'Updated subTitle',
            'episode[bibliography]' => '<p>Updated Text</p>',
            'episode[copyright]' => '<p>Updated Text</p>',
            'episode[transcript]' => '<p>Updated Text</p>',
            'episode[abstract]' => '<p>Updated Text</p>',
        ]);
        $this->overrideField($form, 'episode[season]', 2);
        $this->overrideField($form, 'episode[podcast]', 2);

        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/6', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testAdminDelete() : void {
        /** @var EpisodeRepository $repo */
        $repo = self::$container->get(EpisodeRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/3');
        $this->assertResponseIsSuccessful();
        $form = $crawler->filter('form[action="/episode/3"]')->form();
        $this->client->submit($form);

        $this->assertResponseRedirects('/episode/', Response::HTTP_FOUND);
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }

    public function testAnonNewAudio() : void {
        $crawler = $this->client->request('GET', '/episode/1/new_audio');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserNewAudio() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/1/new_audio');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNewAudio() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/1/new_audio');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(AudioManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Create')->form([
            'audio[public]' => 1,
            'audio[description]' => 'Description',
            'audio[license]' => 'License',
        ]);
        $form['audio[file]']->upload(dirname(__FILE__, 2) . '/data/audio/443027__pramonette__thunder-long.mp3');
        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonEditAudio() : void {
        $crawler = $this->client->request('GET', '/episode/1/edit_audio/1');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEditAudio() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/1/edit_audio/1');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEditAudio() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/1/edit_audio/1');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(AudioManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Update')->form([
            'audio[public]' => 0,
            'audio[description]' => 'Updated Description',
            'audio[license]' => 'Updated License',
        ]);
        $form['audio[newFile]']->upload(dirname(__FILE__, 2) . '/data/audio/443027__pramonette__thunder-long.mp3');
        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonDeleteAudio() : void {
        $crawler = $this->client->request('DELETE', '/episode/1/delete_audio/1');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserDeleteAudio() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('DELETE', '/episode/1/delete_audio/1');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminDeleteAudio() : void {
        $repo = self::$container->get(AudioRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/episode/4/delete_audio/4"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/4');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }

    public function testAdminDeleteWrongAudio() : void {
        $repo = self::$container->get(AudioRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/episode/4/delete_audio/4"]')->form();
        $form->getNode()->setAttribute('action', '/episode/3/delete_audio/4');

        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount, $postCount);
    }

    public function testAdminDeleteAudioWrongToken() : void {
        $repo = self::$container->get(AudioRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/episode/4/delete_audio/4"]')->form([
            '_token' => 'abc123',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/4');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert-warning', 'Invalid security token.');

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount, $postCount);
    }

    public function testAnonNewImage() : void {
        $crawler = $this->client->request('GET', '/episode/1/new_image');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserNewImage() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/1/new_image');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNewImage() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/1/new_image');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(ImageManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Create')->form([
            'image[public]' => 1,
            'image[description]' => 'Description',
            'image[license]' => 'License',
        ]);
        $form['image[file]']->upload(dirname(__FILE__, 2) . '/data/image/28213926366_4430448ff7_c.jpg');
        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonEditImage() : void {
        $crawler = $this->client->request('GET', '/episode/1/edit_image/9');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEditImage() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/1/edit_image/9');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEditImage() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/1/edit_image/9');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(ImageManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Update')->form([
            'image[public]' => 0,
            'image[description]' => 'Updated Description',
            'image[license]' => 'Updated License',
        ]);
        $form['image[newFile]']->upload(dirname(__FILE__, 2) . '/data/image/3632486652_b432f7b283_c.jpg');
        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonDeleteImage() : void {
        $crawler = $this->client->request('DELETE', '/episode/1/delete_image/12');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserDeleteImage() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('DELETE', '/episode/1/delete_image/12');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminDeleteImage() : void {
        $repo = self::$container->get(ImageRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/episode/4/delete_image/12"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/4');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }

    public function testAdminDeleteWrongImage() : void {
        $repo = self::$container->get(ImageRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/episode/4/delete_image/12"]')->form();
        $form->getNode()->setAttribute('action', '/episode/3/delete_image/12');

        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount, $postCount);
    }

    public function testAdminDeleteImageWrongToken() : void {
        $repo = self::$container->get(ImageRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/episode/4/delete_image/12"]')->form([
            '_token' => 'abc123',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/4');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert-warning', 'Invalid security token.');

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount, $postCount);
    }

    public function testAnonNewPdf() : void {
        $crawler = $this->client->request('GET', '/episode/1/new_pdf');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserNewPdf() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/1/new_pdf');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminNewPdf() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/1/new_pdf');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(PdfManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Create')->form([
            'pdf[public]' => 1,
            'pdf[description]' => 'Description',
            'pdf[license]' => 'License',
        ]);
        $form['pdf[file]']->upload(dirname(__FILE__, 2) . '/data/pdf/holmes_2.pdf');
        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonEditPdf() : void {
        $crawler = $this->client->request('GET', '/episode/1/edit_pdf/1');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserEditPdf() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('GET', '/episode/1/edit_pdf/1');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminEditPdf() : void {
        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/1/edit_pdf/1');
        $this->assertResponseIsSuccessful();

        $manager = self::$container->get(PdfManager::class);
        $manager->setCopy(true);

        $form = $crawler->selectButton('Update')->form([
            'pdf[public]' => 0,
            'pdf[description]' => 'Updated Description',
            'pdf[license]' => 'Updated License',
        ]);
        $form['pdf[newFile]']->upload(dirname(__FILE__, 2) . '/data/pdf/holmes_2.pdf');
        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/1');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $manager->setCopy(false);
    }

    public function testAnonDeletePdf() : void {
        $crawler = $this->client->request('DELETE', '/episode/1/delete_pdf/1');
        $this->assertResponseRedirects('/login', Response::HTTP_FOUND);
    }

    public function testUserDeletePdf() : void {
        $this->login(UserFixtures::USER);
        $crawler = $this->client->request('DELETE', '/episode/1/delete_pdf/1');
        $this->assertSame(403, $this->client->getResponse()->getStatusCode());
    }

    public function testAdminDeletePdf() : void {
        $repo = self::$container->get(PdfRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/episode/4/delete_pdf/4"]')->form();
        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/4');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount - 1, $postCount);
    }

    public function testAdminDeleteWrongPdf() : void {
        $repo = self::$container->get(PdfRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/episode/4/delete_pdf/4"]')->form();
        $form->getNode()->setAttribute('action', '/episode/3/delete_pdf/4');

        $this->client->submit($form);
        $this->assertResponseStatusCodeSame(Response::HTTP_NOT_FOUND);

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount, $postCount);
    }

    public function testAdminDeletePdfWrongToken() : void {
        $repo = self::$container->get(PdfRepository::class);
        $preCount = count($repo->findAll());

        $this->login(UserFixtures::ADMIN);
        $crawler = $this->client->request('GET', '/episode/4');
        $this->assertResponseIsSuccessful();

        $form = $crawler->filter('form.delete-form[action="/episode/4/delete_pdf/4"]')->form([
            '_token' => 'abc123',
        ]);

        $this->client->submit($form);
        $this->assertResponseRedirects('/episode/4');
        $responseCrawler = $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('div.alert-warning', 'Invalid security token.');

        $this->em->clear();
        $postCount = count($repo->findAll());
        $this->assertSame($preCount, $postCount);
    }
}
