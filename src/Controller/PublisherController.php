<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Publisher;
use App\Form\PublisherType;
use App\Repository\PublisherRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/publishers')]
#[IsGranted('ROLE_USER')]
class PublisherController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Route(path: '', name: 'publisher_index', methods: ['GET'])]
    #[Template]
    public function index(Request $request, PublisherRepository $publisherRepository) : array {
        $q = $request->query->get('q');
        $query = $q ? $publisherRepository->searchQuery($q) : $publisherRepository->indexQuery();

        return [
            'publishers' => $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]),
            'q' => $q,
        ];
    }

    #[Route(path: '/typeahead', name: 'publisher_typeahead', methods: ['GET'])]
    public function typeahead(Request $request, PublisherRepository $publisherRepository) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];

        foreach ($publisherRepository->typeaheadQuery($q)->execute() as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route(path: '/new', name: 'publisher_new', methods: ['GET', 'POST'])]
    #[Template]
    public function new(EntityManagerInterface $entityManager, Request $request) : array|RedirectResponse {
        $publisher = new Publisher();
        $form = $this->createForm(PublisherType::class, $publisher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($publisher);
            $entityManager->flush();
            $this->addFlash('success', 'Publisher created successfully.');

            return $this->redirectToRoute('publisher_show', ['id' => $publisher->getId()]);
        }

        return [
            'publisher' => $publisher,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'publisher_show', methods: ['GET'])]
    #[Template]
    public function show(Publisher $publisher) : array {
        return [
            'publisher' => $publisher,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'publisher_edit', methods: ['GET', 'POST'])]
    #[Template]
    public function edit(EntityManagerInterface $entityManager, Request $request, Publisher $publisher) : array|RedirectResponse {
        $form = $this->createForm(PublisherType::class, $publisher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Publisher updated successfully.');

            return $this->redirectToRoute('publisher_show', ['id' => $publisher->getId()]);
        }

        return [
            'publisher' => $publisher,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'publisher_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Publisher $publisher) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete_publisher' . $publisher->getId(), $request->request->get('_token'))) {
            $entityManager->remove($publisher);
            $entityManager->flush();
            $this->addFlash('success', 'The publisher has been deleted.');
        }

        return $this->redirectToRoute('publisher_index');
    }
}
