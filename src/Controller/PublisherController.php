<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Podcast;
use App\Entity\Publisher;
use App\Form\PublisherType;
use App\Repository\PublisherRepository;
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
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(path: '/podcasts/{podcast_id}/publishers', requirements: [
    'podcast_id' => Requirement::DIGITS,
])]
#[ParamConverter('podcast', options: ['id' => 'podcast_id'])]
#[IsGranted('access', 'podcast')]
class PublisherController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Route(path: '', name: 'publisher_index', methods: ['GET'])]
    #[Template]
    public function index(Request $request, PublisherRepository $publisherRepository, Podcast $podcast) : array {
        $q = $request->query->get('q');
        $query = $q ? $publisherRepository->searchQuery($podcast, $q) : $publisherRepository->indexQuery($podcast);

        return [
            'podcast' => $podcast,
            'publishers' => $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]),
            'q' => $q,
        ];
    }

    #[Route(path: '/typeahead', name: 'publisher_typeahead', methods: ['GET'])]
    public function typeahead(Request $request, PublisherRepository $publisherRepository, Podcast $podcast) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            $q = '%';
        }
        $data = [];

        foreach ($publisherRepository->typeaheadQuery($podcast, $q)->execute() as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route(path: '/new', name: 'publisher_new', methods: ['GET', 'POST'])]
    #[Template('publisher/new_modal_content.html.twig')]
    public function new(EntityManagerInterface $entityManager, Request $request, Podcast $podcast) : array|JsonResponse {
        $publisher = new Publisher();
        $publisher->setPodcast($podcast);
        $podcast->addAllPublisher($publisher);

        $form = $this->createForm(PublisherType::class, $publisher);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $entityManager->persist($publisher);
                $entityManager->flush();
            }

            return new JsonResponse([
                'success' => $form->isValid(),
                'data' => [
                    'id' => $publisher->getId(),
                    'text' => (string) $publisher,
                ],
            ]);
        }

        return [
            'podcast' => $podcast,
            'publisher' => $publisher,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}/edit', name: 'publisher_edit', methods: ['GET', 'POST'])]
    #[Template]
    public function edit(EntityManagerInterface $entityManager, Request $request, Podcast $podcast, Publisher $publisher) : array|RedirectResponse {
        $form = $this->createForm(PublisherType::class, $publisher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Publisher updated successfully.');

            return $this->redirectToRoute('publisher_index', ['podcast_id' => $podcast->getId()]);
        }

        return [
            'publisher' => $publisher,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'publisher_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Podcast $podcast, Publisher $publisher) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete_publisher' . $publisher->getId(), $request->request->get('_token'))) {
            $entityManager->remove($publisher);
            $entityManager->flush();
            $this->addFlash('success', 'The publisher has been deleted.');
        }

        return $this->redirectToRoute('publisher_index', ['podcast_id' => $podcast->getId()]);
    }
}
