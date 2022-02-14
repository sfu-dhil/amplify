<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Season;
use App\Form\SeasonType;
use App\Repository\SeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\MediaBundle\Controller\ImageControllerTrait;
use Nines\MediaBundle\Entity\Image;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/season")
 */
class SeasonController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;
    use ImageControllerTrait;

    /**
     * @Route("/", name="season_index", methods={"GET"})
     *
     * @Template("season/index.html.twig")
     */
    public function index(Request $request, SeasonRepository $seasonRepository) : array {
        $query = $seasonRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'seasons' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="season_search", methods={"GET"})
     *
     * @Template("season/search.html.twig")
     *
     * @return array
     */
    public function search(Request $request, SeasonRepository $seasonRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $seasonRepository->searchQuery($q);
            $seasons = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $seasons = [];
        }

        return [
            'seasons' => $seasons,
            'q' => $q,
        ];
    }

    /**
     * @Route("/typeahead", name="season_typeahead", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request, SeasonRepository $seasonRepository) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];

        foreach ($seasonRepository->typeaheadQuery($q)->execute() as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="season_new", methods={"GET", "POST"})
     * @Template("season/new.html.twig")
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return array|RedirectResponse
     */
    public function new(Request $request) {
        $season = new Season();
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            foreach ($season->getContributions() as $contribution) {
                $contribution->setSeason($season);
                $entityManager->persist($contribution);
            }
            $entityManager->persist($season);
            $entityManager->flush();
            $this->addFlash('success', 'The new season has been saved.');

            return $this->redirectToRoute('season_show', ['id' => $season->getId()]);
        }

        return [
            'season' => $season,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/new_popup", name="season_new_popup", methods={"GET", "POST"})
     * @Template("season/new_popup.html.twig")
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return array|RedirectResponse
     */
    public function new_popup(Request $request) {
        return $this->new($request);
    }

    /**
     * @Route("/{id}", name="season_show", methods={"GET"})
     * @Template("season/show.html.twig")
     *
     * @return array
     */
    public function show(Season $season) {
        return [
            'season' => $season,
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="season_edit", methods={"GET", "POST"})
     *
     * @Template("season/edit.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function edit(Request $request, Season $season) {
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            foreach ($season->getContributions() as $contribution) {
                $contribution->setSeason($season);
                if ( ! $entityManager->contains($contribution)) {
                    $entityManager->persist($contribution);
                }
            }
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The updated season has been saved.');

            return $this->redirectToRoute('season_show', ['id' => $season->getId()]);
        }

        return [
            'season' => $season,
            'form' => $form->createView(),
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="season_delete", methods={"DELETE"})
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Season $season) {
        if ($this->isCsrfTokenValid('delete' . $season->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($season);
            $entityManager->flush();
            $this->addFlash('success', 'The season has been deleted.');
        }

        return $this->redirectToRoute('season_index');
    }

    /**
     * @Route("/{id}/new_image", name="season_new_image", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @Template("season/new_image.html.twig")
     */
    public function newImage(Request $request, EntityManagerInterface $em, Season $season) {
        return $this->newImageAction($request, $em, $season, 'season_show');
    }

    /**
     * @Route("/{id}/edit_image/{image_id}", name="season_edit_image", methods={"GET", "POST"})
     * @ParamConverter("image", options={"id": "image_id"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @Template("season/edit_image.html.twig")
     */
    public function editImage(Request $request, EntityManagerInterface $em, Season $season, Image $image) {
        return $this->editImageAction($request, $em, $season, $image, 'season_show');
    }

    /**
     * @Route("/{id}/delete_image/{image_id}", name="season_delete_image", methods={"DELETE"})
     * @ParamConverter("image", options={"id": "image_id"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     */
    public function deleteImage(Request $request, EntityManagerInterface $em, Season $season, Image $image) {
        return $this->deleteImageAction($request, $em, $season, $image, 'season_show');
    }
}
