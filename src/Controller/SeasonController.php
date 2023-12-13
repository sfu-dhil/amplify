<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Podcast;
use App\Entity\Season;
use App\Form\SeasonType;
use App\Repository\SeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(path: '/podcasts/{podcast_id}/seasons', requirements: [
    'podcast_id' => Requirement::DIGITS,
])]
#[ParamConverter('podcast', options: ['id' => 'podcast_id'])]
#[IsGranted('access', 'podcast')]
class SeasonController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Route(path: '/typeahead', name: 'season_typeahead', methods: ['GET'])]
    public function typeahead(Request $request, SeasonRepository $seasonRepository, Podcast $podcast) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            $q = '%';
        }

        $data = [];
        foreach ($seasonRepository->typeaheadQuery($podcast, $q)->execute() as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route(path: '/new', name: 'season_new', methods: ['GET', 'POST'])]
    #[Template]
    public function new(EntityManagerInterface $entityManager, Request $request, Podcast $podcast) : array|RedirectResponse {
        $season = new Season();
        $season->setPodcast($podcast);
        $podcast->addSeason($season);

        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($season->getContributions() as $contribution) {
                $contribution->setSeason($season);
                $entityManager->persist($contribution);
            }
            $entityManager->persist($season);
            $entityManager->flush();

            foreach ($season->getImages() as $image) {
                $image->setEntity($season);
                $entityManager->persist($image);
            }
            $season->updateStatus();
            $entityManager->flush();
            $this->addFlash('success', 'Season created successfully.');

            if ($request->request->has('submitAndContinue')) {
                return $this->redirectToRoute('season_edit', ['podcast_id' => $podcast->getId(), 'id' => $season->getId()]);
            }

            return $this->redirectToRoute('podcast_show', ['id' => $podcast->getId()]);

        }

        return [
            'season' => $season,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'season_show', methods: ['GET'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[Template]
    public function show(Podcast $podcast, Season $season) : array|RedirectResponse {
        if ($season->getPodcast() !== $podcast) {
            throw new ResourceNotFoundException();
        }

        return [
            'season' => $season,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'season_edit', methods: ['GET', 'POST'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[Template]
    public function edit(EntityManagerInterface $entityManager, Request $request, Podcast $podcast, Season $season) : array|RedirectResponse {
        if ($season->getPodcast() !== $podcast) {
            throw new ResourceNotFoundException();
        }

        $existingImages = $season->getImages();
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($season->getContributions() as $contribution) {
                $contribution->setSeason($season);
                if ( ! $entityManager->contains($contribution)) {
                    $entityManager->persist($contribution);
                }
            }
            $currentImageIds = [];
            foreach ($season->getImages() as $image) {
                $image->setEntity($season);
                $entityManager->persist($image);
                $currentImageIds[] = $image->getId();
            }
            foreach ($existingImages as $existingImage) {
                if ( ! in_array($existingImage->getId(), $currentImageIds, true)) {
                    $entityManager->remove($existingImage);
                }
            }
            $season->updateStatus();
            $entityManager->flush();
            $this->addFlash('success', 'Season updated successfully.');

            if ($request->request->has('submitAndContinue')) {
                return $this->redirectToRoute('season_edit', ['podcast_id' => $podcast->getId(), 'id' => $season->getId()]);
            }

            return $this->redirectToRoute('season_show', ['podcast_id' => $podcast->getId(), 'id' => $season->getId()]);

        }

        return [
            'season' => $season,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'season_delete', methods: ['DELETE'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Podcast $podcast, Season $season) : RedirectResponse {
        if ($season->getPodcast() !== $podcast) {
            throw new ResourceNotFoundException();
        }

        if ($this->isCsrfTokenValid('delete_season' . $season->getId(), $request->request->get('_token'))) {
            $entityManager->remove($season);
            $entityManager->flush();
            $this->addFlash('success', 'The season has been deleted.');
        }

        return $this->redirectToRoute('podcast_show', ['id' => $podcast->getId()]);
    }
}
