<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Institution;
use App\Form\InstitutionType;
use App\Repository\InstitutionRepository;
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

#[Route(path: '/institutions')]
#[IsGranted('ROLE_USER')]
class InstitutionController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Route(path: '', name: 'institution_index', methods: ['GET'])]
    #[Template]
    public function index(Request $request, InstitutionRepository $institutionRepository) : array {
        $q = $request->query->get('q');
        $query = $q ? $institutionRepository->searchQuery($q) : $institutionRepository->indexQuery();

        return [
            'institutions' => $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]),
            'q' => $q,
        ];
    }

    #[Route(path: '/typeahead', name: 'institution_typeahead', methods: ['GET'])]
    public function typeahead(Request $request, InstitutionRepository $institutionRepository) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];

        foreach ($institutionRepository->typeaheadQuery($q)->execute() as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route(path: '/new', name: 'institution_new', methods: ['GET', 'POST'])]
    #[Template]
    public function new(EntityManagerInterface $entityManager, Request $request) : array|RedirectResponse {
        $institution = new Institution();
        $form = $this->createForm(InstitutionType::class, $institution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($institution);
            $entityManager->flush();
            $this->addFlash('success', 'Institution created successfully.');

            return $this->redirectToRoute('institution_show', ['id' => $institution->getId()]);
        }

        return [
            'institution' => $institution,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'institution_show', methods: ['GET'])]
    #[Template]
    public function show(Institution $institution) : array {
        return [
            'institution' => $institution,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'institution_edit', methods: ['GET', 'POST'])]
    #[Template]
    public function edit(EntityManagerInterface $entityManager, Request $request, Institution $institution) : array|RedirectResponse {
        $form = $this->createForm(InstitutionType::class, $institution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Institution updated successfully.');

            return $this->redirectToRoute('institution_show', ['id' => $institution->getId()]);
        }

        return [
            'institution' => $institution,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'institution_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Institution $institution) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete_institution' . $institution->getId(), $request->request->get('_token'))) {
            $entityManager->remove($institution);
            $entityManager->flush();
            $this->addFlash('success', 'The institution has been deleted.');
        }

        return $this->redirectToRoute('institution_index');
    }
}
