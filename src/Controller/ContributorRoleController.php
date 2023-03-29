<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\ContributorRole;
use App\Form\ContributorRoleType;
use App\Repository\ContributorRoleRepository;
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

#[Route(path: '/contributor_role')]
class ContributorRoleController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Route(path: '/', name: 'contributor_role_index', methods: ['GET'])]
    #[Template]
    public function index(Request $request, ContributorRoleRepository $contributorRoleRepository) : array {
        $query = $contributorRoleRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'contributor_roles' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @return array
     */
    #[Route(path: '/search', name: 'contributor_role_search', methods: ['GET'])]
    #[Template]
    public function search(Request $request, ContributorRoleRepository $contributorRoleRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $contributorRoleRepository->searchQuery($q);
            $contributorRoles = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $contributorRoles = [];
        }

        return [
            'contributor_roles' => $contributorRoles,
            'q' => $q,
        ];
    }

    /**
     * @return JsonResponse
     */
    #[Route(path: '/typeahead', name: 'contributor_role_typeahead', methods: ['GET'])]
    public function typeahead(Request $request, ContributorRoleRepository $contributorRoleRepository) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];

        foreach ($contributorRoleRepository->typeaheadQuery($q)->execute() as $result) {
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
    #[Route(path: '/new', name: 'contributor_role_new', methods: ['GET', 'POST'])]
    #[Template]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function new(EntityManagerInterface $entityManager, Request $request) {
        $contributorRole = new ContributorRole();
        $form = $this->createForm(ContributorRoleType::class, $contributorRole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contributorRole);
            $entityManager->flush();
            $this->addFlash('success', 'The new contributorRole has been saved.');

            return $this->redirectToRoute('contributor_role_show', ['id' => $contributorRole->getId()]);
        }

        return [
            'contributor_role' => $contributorRole,
            'form' => $form->createView(),
        ];
    }

    /**
     * @return array|RedirectResponse
     */
    #[Route(path: '/new_popup', name: 'contributor_role_new_popup', methods: ['GET', 'POST'])]
    #[Template]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function new_popup(EntityManagerInterface $entityManager, Request $request) {
        return $this->new($entityManager, $request);
    }

    /**
     * @return array
     */
    #[Route(path: '/{id}', name: 'contributor_role_show', methods: ['GET'])]
    #[Template]
    public function show(ContributorRole $contributorRole) {
        return [
            'contributor_role' => $contributorRole,
        ];
    }

    /**
     * @return array|RedirectResponse
     */
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Route(path: '/{id}/edit', name: 'contributor_role_edit', methods: ['GET', 'POST'])]
    #[Template]
    public function edit(EntityManagerInterface $entityManager, Request $request, ContributorRole $contributorRole) {
        $form = $this->createForm(ContributorRoleType::class, $contributorRole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'The updated contributorRole has been saved.');

            return $this->redirectToRoute('contributor_role_show', ['id' => $contributorRole->getId()]);
        }

        return [
            'contributor_role' => $contributorRole,
            'form' => $form->createView(),
        ];
    }

    /**
     * @return RedirectResponse
     */
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Route(path: '/{id}', name: 'contributor_role_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, Request $request, ContributorRole $contributorRole) {
        if ($this->isCsrfTokenValid('delete' . $contributorRole->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contributorRole);
            $entityManager->flush();
            $this->addFlash('success', 'The contributorRole has been deleted.');
        }

        return $this->redirectToRoute('contributor_role_index');
    }
}
