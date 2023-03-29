<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Season;
use App\Form\SeasonType;
use App\Repository\SeasonRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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

#[Route(path: '/season')]
class SeasonController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;
    use ImageControllerTrait;

    #[Route(path: '/', name: 'season_index', methods: ['GET'])]
    #[Template('season/index.html.twig')]
    public function index(Request $request, SeasonRepository $seasonRepository) : array {
        $query = $seasonRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'seasons' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @return array
     */
    #[Route(path: '/search', name: 'season_search', methods: ['GET'])]
    #[Template('season/search.html.twig')]
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
     * @return JsonResponse
     */
    #[Route(path: '/typeahead', name: 'season_typeahead', methods: ['GET'])]
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
     * @return array|RedirectResponse
     */
    #[Route(path: '/new', name: 'season_new', methods: ['GET', 'POST'])]
    #[Template('season/new.html.twig')]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function new(EntityManagerInterface $entityManager, Request $request) {
        $season = new Season();
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
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
     * @return array|RedirectResponse
     */
    #[Route(path: '/new_popup', name: 'season_new_popup', methods: ['GET', 'POST'])]
    #[Template('season/new_popup.html.twig')]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function new_popup(EntityManagerInterface $entityManager, Request $request) {
        return $this->new($entityManager, $request);
    }

    /**
     * @return array
     */
    #[Route(path: '/{id}', name: 'season_show', methods: ['GET'])]
    #[Template('season/show.html.twig')]
    public function show(Season $season) {
        return [
            'season' => $season,
        ];
    }

    /**
     * @return array|RedirectResponse
     */
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Route(path: '/{id}/edit', name: 'season_edit', methods: ['GET', 'POST'])]
    #[Template('season/edit.html.twig')]
    public function edit(EntityManagerInterface $entityManager, Request $request, Season $season) {
        $form = $this->createForm(SeasonType::class, $season);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($season->getContributions() as $contribution) {
                $contribution->setSeason($season);
                if ( ! $entityManager->contains($contribution)) {
                    $entityManager->persist($contribution);
                }
            }
            $entityManager->flush();
            $this->addFlash('success', 'The updated season has been saved.');

            return $this->redirectToRoute('season_show', ['id' => $season->getId()]);
        }

        return [
            'season' => $season,
            'form' => $form->createView(),
        ];
    }

    /**
     * @return RedirectResponse
     */
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Route(path: '/{id}', name: 'season_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Season $season) {
        if ($this->isCsrfTokenValid('delete' . $season->getId(), $request->request->get('_token'))) {
            $entityManager->remove($season);
            $entityManager->flush();
            $this->addFlash('success', 'The season has been deleted.');
        }

        return $this->redirectToRoute('season_index');
    }

    /**
     * @throws Exception
     *
     * @return array<string,mixed>|RedirectResponse
     */
    #[Route(path: '/{id}/new_image', name: 'season_new_image', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Template('season/new_image.html.twig')]
    public function newImage(Request $request, EntityManagerInterface $em, Season $season) {
        return $this->newImageAction($request, $em, $season, 'season_show');
    }

    /**
     * @throws Exception
     *
     * @return array<string,mixed>|RedirectResponse
     */
    #[Route(path: '/{id}/edit_image/{image_id}', name: 'season_edit_image', methods: ['GET', 'POST'])]
    #[ParamConverter('image', options: ['id' => 'image_id'])]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Template('season/edit_image.html.twig')]
    public function editImage(Request $request, EntityManagerInterface $em, Season $season, Image $image) {
        return $this->editImageAction($request, $em, $season, $image, 'season_show');
    }

    /**
     * @return RedirectResponse
     */
    #[Route(path: '/{id}/delete_image/{image_id}', name: 'season_delete_image', methods: ['DELETE'])]
    #[ParamConverter('image', options: ['id' => 'image_id'])]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function deleteImage(Request $request, EntityManagerInterface $em, Season $season, Image $image) {
        return $this->deleteImageAction($request, $em, $season, $image, 'season_show');
    }
}
