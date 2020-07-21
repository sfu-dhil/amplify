<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Episode;
use App\Form\EpisodeType;
use App\Repository\EpisodeRepository;
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
 * @Route("/episode")
 */
class EpisodeController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="episode_index", methods={"GET"})
     *
     * @Template()
     */
    public function index(Request $request, EpisodeRepository $episodeRepository) : array {
        $query = $episodeRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'episodes' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="episode_search", methods={"GET"})
     *
     * @Template()
     *
     * @return array
     */
    public function search(Request $request, EpisodeRepository $episodeRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $episodeRepository->searchQuery($q);
            $episodes = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $episodes = [];
        }

        return [
            'episodes' => $episodes,
            'q' => $q,
        ];
    }

    /**
     * @Route("/typeahead", name="episode_typeahead", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request, EpisodeRepository $episodeRepository) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($episodeRepository->typeaheadSearch($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="episode_new", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return array|RedirectResponse
     */
    public function new(Request $request) {
        $episode = new Episode();
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($episode);
            $entityManager->flush();
            $this->addFlash('success', 'The new episode has been saved.');

            return $this->redirectToRoute('episode_show', ['id' => $episode->getId()]);
        }

        return [
            'episode' => $episode,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/new_popup", name="episode_new_popup", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return array|RedirectResponse
     */
    public function new_popup(Request $request) {
        return $this->new($request);
    }

    /**
     * @Route("/{id}", name="episode_show", methods={"GET"})
     * @Template()
     *
     * @return array
     */
    public function show(Episode $episode) {
        return [
            'episode' => $episode,
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="episode_edit", methods={"GET","POST"})
     *
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function edit(Request $request, Episode $episode) {
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The updated episode has been saved.');

            return $this->redirectToRoute('episode_show', ['id' => $episode->getId()]);
        }

        return [
            'episode' => $episode,
            'form' => $form->createView(),
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="episode_delete", methods={"DELETE"})
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Episode $episode) {
        if ($this->isCsrfTokenValid('delete' . $episode->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($episode);
            $entityManager->flush();
            $this->addFlash('success', 'The episode has been deleted.');
        }

        return $this->redirectToRoute('episode_index');
    }
}
