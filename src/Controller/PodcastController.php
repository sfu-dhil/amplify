<?php

namespace App\Controller;

use App\Entity\Podcast;
use App\Form\PodcastType;
use App\Repository\PodcastRepository;

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
 * @Route("/podcast")
 * @IsGranted("ROLE_USER")
 */
class PodcastController extends AbstractController implements PaginatorAwareInterface
{
    use PaginatorTrait;

    /**
     * @Route("/", name="podcast_index", methods={"GET"})
     * @param Request $request
     * @param PodcastRepository $podcastRepository
     *
     * @Template()
     *
     * @return array
     */
    public function index(Request $request, PodcastRepository $podcastRepository) : array
    {
        $query = $podcastRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'podcasts' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="podcast_search", methods={"GET"})
     *
     * @Template()
     *
     * @return array
     */
    public function search(Request $request, PodcastRepository $podcastRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $podcastRepository->searchQuery($q);
            $podcasts = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), array('wrap-queries'=>true));
        } else {
            $podcasts = [];
        }

        return [
            'podcasts' => $podcasts,
            'q' => $q,
        ];
    }

    /**
     * @Route("/typeahead", name="podcast_typeahead", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request, PodcastRepository $podcastRepository) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($podcastRepository->typeaheadSearch($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string)$result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="podcast_new", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function new(Request $request) {
        $podcast = new Podcast();
        $form = $this->createForm(PodcastType::class, $podcast);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($podcast);
            $entityManager->flush();
            $this->addFlash('success', 'The new podcast has been saved.');

            return $this->redirectToRoute('podcast_show', ['id' => $podcast->getId()]);
        }

        return [
            'podcast' => $podcast,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/new_popup", name="podcast_new_popup", methods={"GET","POST"})
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
     * @Route("/{id}", name="podcast_show", methods={"GET"})
     * @Template()
     * @param Podcast $podcast
     *
     * @return array
     */
    public function show(Podcast $podcast) {
        return [
            'podcast' => $podcast,
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="podcast_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Podcast $podcast
     *
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function edit(Request $request, Podcast $podcast) {
        $form = $this->createForm(PodcastType::class, $podcast);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The updated podcast has been saved.');

            return $this->redirectToRoute('podcast_show', ['id' => $podcast->getId()]);
        }

        return [
            'podcast' => $podcast,
            'form' => $form->createView()
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="podcast_delete", methods={"DELETE"})
     * @param Request $request
     * @param Podcast $podcast
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Podcast $podcast) {
        if ($this->isCsrfTokenValid('delete' . $podcast->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($podcast);
            $entityManager->flush();
            $this->addFlash('success', 'The podcast has been deleted.');
        }

        return $this->redirectToRoute('podcast_index');
    }
}
