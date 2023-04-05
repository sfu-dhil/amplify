<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Person;
use App\Form\PersonType;
use App\Repository\PersonRepository;
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

#[Route(path: '/people')]
class PersonController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Route(path: '', name: 'person_index', methods: ['GET'])]
    #[Template]
    public function index(Request $request, PersonRepository $personRepository) : array {
        $q = $request->query->get('q');
        $query = $q ? $personRepository->searchQuery($q) : $personRepository->indexQuery();

        return [
            'people' => $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]),
            'q' => $q,
        ];
    }

    #[Route(path: '/typeahead', name: 'person_typeahead', methods: ['GET'])]
    public function typeahead(Request $request, PersonRepository $personRepository) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];

        foreach ($personRepository->typeaheadQuery($q)->execute() as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route(path: '/new', name: 'person_new', methods: ['GET', 'POST'])]
    #[Template]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function new(EntityManagerInterface $entityManager, Request $request) : array|RedirectResponse {
        $person = new Person();
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($person);
            $entityManager->flush();
            $this->addFlash('success', 'Person created successfully.');

            return $this->redirectToRoute('person_show', ['id' => $person->getId()]);
        }

        return [
            'person' => $person,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'person_show', methods: ['GET'])]
    #[Template]
    public function show(Person $person) : array {
        return [
            'person' => $person,
        ];
    }

    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Route(path: '/{id}/edit', name: 'person_edit', methods: ['GET', 'POST'])]
    #[Template]
    public function edit(EntityManagerInterface $entityManager, Request $request, Person $person) : array|RedirectResponse {
        $form = $this->createForm(PersonType::class, $person);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Person updated successfully.');

            return $this->redirectToRoute('person_show', ['id' => $person->getId()]);
        }

        return [
            'person' => $person,
            'form' => $form->createView(),
        ];
    }

    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Route(path: '/{id}', name: 'person_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Person $person) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete_person' . $person->getId(), $request->request->get('_token'))) {
            $entityManager->remove($person);
            $entityManager->flush();
            $this->addFlash('success', 'The person has been deleted.');
        }

        return $this->redirectToRoute('person_index');
    }
}
