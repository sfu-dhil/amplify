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

#[Route(path: '/contributor_roles')]
#[IsGranted('ROLE_USER')]
class ContributorRoleController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Route(path: '', name: 'contributor_role_index', methods: ['GET'])]
    #[Template]
    public function index(Request $request, ContributorRoleRepository $contributorRoleRepository) : array {
        $q = $request->query->get('q');
        $query = $q ? $contributorRoleRepository->searchQuery($q) : $contributorRoleRepository->indexQuery();

        return [
            'contributor_roles' => $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]),
            'q' => $q,
        ];
    }

    #[Route(path: '/typeahead', name: 'contributor_role_typeahead', methods: ['GET'])]
    public function typeahead(Request $request, ContributorRoleRepository $contributorRoleRepository) : JsonResponse {
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

    #[Route(path: '/new', name: 'contributor_role_new', methods: ['GET', 'POST'])]
    #[Template]
    public function new(EntityManagerInterface $entityManager, Request $request) : array|RedirectResponse {
        $contributorRole = new ContributorRole();
        $form = $this->createForm(ContributorRoleType::class, $contributorRole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($contributorRole);
            $entityManager->flush();
            $this->addFlash('success', 'Contributor role created successfully.');

            return $this->redirectToRoute('contributor_role_show', ['id' => $contributorRole->getId()]);
        }

        return [
            'contributor_role' => $contributorRole,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'contributor_role_show', methods: ['GET'])]
    #[Template]
    public function show(ContributorRole $contributorRole) : array {
        return [
            'contributor_role' => $contributorRole,
        ];
    }

    #[Route(path: '/{id}/edit', name: 'contributor_role_edit', methods: ['GET', 'POST'])]
    #[Template]
    public function edit(EntityManagerInterface $entityManager, Request $request, ContributorRole $contributorRole) : array|RedirectResponse {
        $form = $this->createForm(ContributorRoleType::class, $contributorRole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Contributor role updated successfully.');

            return $this->redirectToRoute('contributor_role_show', ['id' => $contributorRole->getId()]);
        }

        return [
            'contributor_role' => $contributorRole,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'contributor_role_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, Request $request, ContributorRole $contributorRole) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete_contributor_role' . $contributorRole->getId(), $request->request->get('_token'))) {
            $entityManager->remove($contributorRole);
            $entityManager->flush();
            $this->addFlash('success', 'The contributorRole has been deleted.');
        }

        return $this->redirectToRoute('contributor_role_index');
    }
}
