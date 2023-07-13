<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Podcast;
use App\Form\EpisodeType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(path: '/podcasts/{podcast_id}/episodes', requirements: [
    'podcast_id' => Requirement::DIGITS,
])]
#[ParamConverter('podcast', options: ['id' => 'podcast_id'])]
#[IsGranted('access', 'podcast')]
class EpisodeController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Route(path: '/new', name: 'episode_new', methods: ['GET', 'POST'])]
    #[Template]
    public function new(EntityManagerInterface $entityManager, Request $request, Podcast $podcast) : array|RedirectResponse {
        $episode = new Episode();
        $episode->setPodcast($podcast);
        $podcast->addEpisode($episode);

        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($episode->getContributions() as $contribution) {
                $contribution->setEpisode($episode);
                $entityManager->persist($contribution);
            }
            foreach ($episode->getAudios() as $audio) {
                $audio->setEntity($episode);
                $entityManager->persist($audio);
            }
            foreach ($episode->getImages() as $image) {
                $image->setEntity($episode);
                $entityManager->persist($image);
            }
            foreach ($episode->getPdfs() as $pdf) {
                $pdf->setEntity($episode);
                $entityManager->persist($pdf);
            }
            $entityManager->persist($episode);
            $entityManager->flush();
            $this->addFlash('success', 'Episode created successfully.');

            return $this->redirectToRoute('podcast_show', ['id' => $podcast->getId()]);
        }

        return [
            'episode' => $episode,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'episode_show', methods: ['GET'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[Template]
    public function show(Podcast $podcast, Episode $episode) : array|RedirectResponse {
        if ($episode->getPodcast() !== $podcast) {
            throw new ResourceNotFoundException();
        }

        return [
            'episode' => $episode,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'episode_edit', methods: ['GET', 'POST'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[Template]
    public function edit(EntityManagerInterface $entityManager, Request $request, Podcast $podcast, Episode $episode) : array|RedirectResponse {
        if ($episode->getPodcast() !== $podcast) {
            throw new ResourceNotFoundException();
        }

        $existingAudios = $episode->getAudios();
        $existingImages = $episode->getImages();
        $existingPdfs = $episode->getPdfs();
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($episode->getContributions() as $contribution) {
                $contribution->setEpisode($episode);
                if ( ! $entityManager->contains($contribution)) {
                    $entityManager->persist($contribution);
                }
            }
            $currentAudioIds = [];
            foreach ($episode->getAudios() as $audio) {
                $audio->setEntity($episode);
                $entityManager->persist($audio);
                $currentAudioIds[] = $audio->getId();
            }
            foreach ($existingAudios as $existingAudio) {
                if ( ! in_array($existingAudio->getId(), $currentAudioIds, true)) {
                    $entityManager->remove($existingAudio);
                }
            }
            $currentImageIds = [];
            foreach ($episode->getImages() as $image) {
                $image->setEntity($episode);
                $entityManager->persist($image);
                $currentImageIds[] = $image->getId();
            }
            foreach ($existingImages as $existingImage) {
                if ( ! in_array($existingImage->getId(), $currentImageIds, true)) {
                    $entityManager->remove($existingImage);
                }
            }
            $currentPdfIds = [];
            foreach ($episode->getPdfs() as $pdf) {
                $pdf->setEntity($episode);
                $entityManager->persist($pdf);
                $currentPdfIds[] = $pdf->getId();
            }
            foreach ($existingPdfs as $existingPdf) {
                if ( ! in_array($existingPdf->getId(), $currentPdfIds, true)) {
                    $entityManager->remove($existingPdf);
                }
            }
            $entityManager->flush();
            $this->addFlash('success', 'Episode updated successfully.');

            return $this->redirectToRoute('episode_show', ['podcast_id' => $podcast->getId(), 'id' => $episode->getId()]);
        }

        return [
            'episode' => $episode,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'episode_delete', methods: ['DELETE'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Podcast $podcast, Episode $episode) : RedirectResponse {
        if ($episode->getPodcast() !== $podcast) {
            throw new ResourceNotFoundException();
        }

        if ($this->isCsrfTokenValid('delete_episode' . $episode->getId(), $request->request->get('_token'))) {
            $entityManager->remove($episode);
            $entityManager->flush();
            $this->addFlash('success', 'The episode has been deleted.');
        }

        return $this->redirectToRoute('podcast_show', ['id' => $podcast->getId()]);
    }
}
