<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\ContributorRole;
use App\Form\ContributorRoleType;
use App\Repository\ContributorRoleRepository;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contributor_role")
 */
class ContributorRoleController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="contributor_role_index", methods={"GET"})
     *
     * @Template()
     */
    public function index(Request $request, ContributorRoleRepository $contributorRoleRepository) : array {
        $query = $contributorRoleRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'contributor_roles' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="contributor_role_search", methods={"GET"})
     *
     * @Template()
     *
     * @return array
     */
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
     * @Route("/typeahead", name="contributor_role_typeahead", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request, ContributorRoleRepository $contributorRoleRepository) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($contributorRoleRepository->typeaheadSearch($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="contributor_role_new", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return array|RedirectResponse
     */
    public function new(Request $request) {
        $contributorRole = new ContributorRole();
        $form = $this->createForm(ContributorRoleType::class, $contributorRole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contributorRole);
            $entityManager->flush();
            $this->addFlash('success', 'The new contributorRole has been saved.');

            return $this->redirectToRoute('contributor_role_show', ['id' => $contributorRole->getId()]);
        }

        return [
            'contributorRole' => $contributorRole,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/new_popup", name="contributor_role_new_popup", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return array|RedirectResponse
     */
    public function new_popup(Request $request) {
        return $this->new($request);
    }

    /**
     * @Route("/{id}", name="contributor_role_show", methods={"GET"})
     * @Template()
     *
     * @return array
     */
    public function show(ContributorRole $contributorRole) {
        return [
            'contributor_role' => $contributorRole,
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="contributor_role_edit", methods={"GET","POST"})
     *
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function edit(Request $request, ContributorRole $contributorRole) {
        $form = $this->createForm(ContributorRoleType::class, $contributorRole);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The updated contributorRole has been saved.');

            return $this->redirectToRoute('contributor_role_show', ['id' => $contributorRole->getId()]);
        }

        return [
            'contributor_role' => $contributorRole,
            'form' => $form->createView(),
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="contributor_role_delete", methods={"DELETE"})
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, ContributorRole $contributorRole) {
        if ($this->isCsrfTokenValid('delete' . $contributorRole->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($contributorRole);
            $entityManager->flush();
            $this->addFlash('success', 'The contributorRole has been deleted.');
        }

        return $this->redirectToRoute('contributor_role_index');
    }
}
