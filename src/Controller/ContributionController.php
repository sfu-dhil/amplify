<?php

namespace App\Controller;

use App\Entity\Contribution;
use App\Form\ContributionType;
use App\Repository\ContributionRepository;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/contribution")
 * @IsGranted("ROLE_USER")
 */
class ContributionController extends AbstractController implements PaginatorAwareInterface
{
    use PaginatorTrait;

    /**
     * @Route("/", name="contribution_index", methods={"GET"})
     * @param Request $request
     * @param ContributionRepository $contributionRepository
     *
     * @Template()
     *
     * @return array
     */
    public function index(Request $request, ContributionRepository $contributionRepository) : array
    {
        $query = $contributionRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'contributions' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="contribution_search", methods={"GET"})
     *
     * @Template()
     *
     * @return array
     */
    public function search(Request $request, ContributionRepository $contributionRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $contributionRepository->searchQuery($q);
            $contributions = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), array('wrap-queries'=>true));
        } else {
            $contributions = [];
        }

        return [
            'contributions' => $contributions,
            'q' => $q,
        ];
    }

    /**
     * @Route("/typeahead", name="contribution_typeahead", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request, ContributionRepository $contributionRepository) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($contributionRepository->typeaheadSearch($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string)$result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="contribution_new", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function new(Request $request) {
        $contribution = new Contribution();
        $form = $this->createForm(ContributionType::class, $contribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($contribution);
            $entityManager->flush();
            $this->addFlash('success', 'The new contribution has been saved.');

            return $this->redirectToRoute('contribution_show', ['id' => $contribution->getId()]);
        }

        return [
            'contribution' => $contribution,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/new_popup", name="contribution_new_popup", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function new_popup(Request $request) {
        return $this->new($request);
    }

    /**
     * @Route("/{id}", name="contribution_show", methods={"GET"})
     * @Template()
     * @param Contribution $contribution
     *
     * @return array
     */
    public function show(Contribution $contribution) {
        return [
            'contribution' => $contribution,
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="contribution_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Contribution $contribution
     *
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function edit(Request $request, Contribution $contribution) {
        $form = $this->createForm(ContributionType::class, $contribution);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The updated contribution has been saved.');

            return $this->redirectToRoute('contribution_show', ['id' => $contribution->getId()]);
        }

        return [
            'contribution' => $contribution,
            'form' => $form->createView()
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="contribution_delete", methods={"DELETE"})
     * @param Request $request
     * @param Contribution $contribution
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Contribution $contribution) {
        if ($this->isCsrfTokenValid('delete' . $contribution->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($contribution);
            $entityManager->flush();
            $this->addFlash('success', 'The contribution has been deleted.');
        }

        return $this->redirectToRoute('contribution_index');
    }
}
