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

#[Route(path: '/institution')]
class InstitutionController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Route(path: '/', name: 'institution_index', methods: ['GET'])]
    #[Template]
    public function index(Request $request, InstitutionRepository $institutionRepository) : array {
        $query = $institutionRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'institutions' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @return array
     */
    #[Route(path: '/search', name: 'institution_search', methods: ['GET'])]
    #[Template]
    public function search(Request $request, InstitutionRepository $institutionRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $institutionRepository->searchQuery($q);
            $institutions = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $institutions = [];
        }

        return [
            'institutions' => $institutions,
            'q' => $q,
        ];
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/typeahead', name: 'institution_typeahead', methods: ['GET'])]
    public function typeahead(Request $request, InstitutionRepository $institutionRepository) {
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

    /**
     * @return array|RedirectResponse
     */
    #[Route(path: '/new', name: 'institution_new', methods: ['GET', 'POST'])]
    #[Template]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function new(EntityManagerInterface $entityManager, Request $request) {
        $institution = new Institution();
        $form = $this->createForm(InstitutionType::class, $institution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($institution);
            $entityManager->flush();
            $this->addFlash('success', 'The new institution has been saved.');

            return $this->redirectToRoute('institution_show', ['id' => $institution->getId()]);
        }

        return [
            'institution' => $institution,
            'form' => $form->createView(),
        ];
    }

    /**
     * @return array|RedirectResponse
     */
    #[Route(path: '/new_popup', name: 'institution_new_popup', methods: ['GET', 'POST'])]
    #[Template]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function new_popup(EntityManagerInterface $entityManager, Request $request) {
        return $this->new($entityManager, $request);
    }

    /**
     * @return array
     */
    #[Route(path: '/{id}', name: 'institution_show', methods: ['GET'])]
    #[Template]
    public function show(Institution $institution) {
        return [
            'institution' => $institution,
        ];
    }

    /**
     * @return array|RedirectResponse
     */
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Route(path: '/{id}/edit', name: 'institution_edit', methods: ['GET', 'POST'])]
    #[Template]
    public function edit(EntityManagerInterface $entityManager, Request $request, Institution $institution) {
        $form = $this->createForm(InstitutionType::class, $institution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'The updated institution has been saved.');

            return $this->redirectToRoute('institution_show', ['id' => $institution->getId()]);
        }

        return [
            'institution' => $institution,
            'form' => $form->createView(),
        ];
    }

    /**
     * @return RedirectResponse
     */
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Route(path: '/{id}', name: 'institution_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Institution $institution) {
        if ($this->isCsrfTokenValid('delete' . $institution->getId(), $request->request->get('_token'))) {
            $entityManager->remove($institution);
            $entityManager->flush();
            $this->addFlash('success', 'The institution has been deleted.');
        }

        return $this->redirectToRoute('institution_index');
    }
}
