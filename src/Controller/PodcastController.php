<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Podcast;
use App\Form\PodcastType;
use App\Repository\PodcastRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(path: '/podcasts')]
#[IsGranted('ROLE_USER')]
class PodcastController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Route(path: '', name: 'podcast_index', methods: ['GET'])]
    #[Template]
    public function index(Request $request, PodcastRepository $podcastRepository) : array|RedirectResponse {
        $q = $request->query->get('q');

        if ($this->isGranted('ROLE_ADMIN')) {
            $query = $q ? $podcastRepository->searchQuery($q) : $podcastRepository->indexQuery();
        } else {
            $query = $q ? $podcastRepository->searchUserQuery($this->getUser(), $q) : $podcastRepository->indexUserQuery($this->getUser());
        }

        return [
            'podcasts' => $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]),
            'q' => $q,
        ];
    }

    #[Route(path: '/{id}', name: 'podcast_show', methods: ['GET'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[Template]
    #[IsGranted('access', 'podcast')]
    public function show(Podcast $podcast) : array|RedirectResponse {
        return [
            'podcast' => $podcast,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'podcast_edit', methods: ['GET', 'POST'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[Template]
    #[IsGranted('access', 'podcast')]
    public function edit(EntityManagerInterface $entityManager, Request $request, Podcast $podcast) : array|RedirectResponse {
        $existingImages = $podcast->getImages();
        $form = $this->createForm(PodcastType::class, $podcast);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($podcast->getContributions() as $contribution) {
                $contribution->setPodcast($podcast);
                if ( ! $entityManager->contains($contribution)) {
                    $entityManager->persist($contribution);
                }
            }
            $currentImageIds = [];
            foreach ($podcast->getImages() as $image) {
                $image->setEntity($podcast);
                $entityManager->persist($image);
                $currentImageIds[] = $image->getId();
            }
            foreach ($existingImages as $existingImage) {
                if ( ! in_array($existingImage->getId(), $currentImageIds, true)) {
                    $entityManager->remove($existingImage);
                }
            }
            $podcast->updateStatus();
            $entityManager->flush();
            $this->addFlash('success', 'Podcast updated successfully.');

            if ($request->request->has('submitAndContinue')) {
                return $this->redirectToRoute('podcast_edit', ['id' => $podcast->getId()]);
            }

            return $this->redirectToRoute('podcast_show', ['id' => $podcast->getId()]);

        }

        return [
            'podcast' => $podcast,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'podcast_delete', methods: ['DELETE'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[IsGranted('access', 'podcast')]
    public function delete(EntityManagerInterface $entityManager, Request $request, Podcast $podcast) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete_podcast' . $podcast->getId(), $request->request->get('_token'))) {
            $entityManager->remove($podcast);
            $entityManager->flush();
            $this->addFlash('success', 'The podcast has been deleted.');
        }

        return $this->redirectToRoute('podcast_index');
    }
}
