<?php

declare(strict_types=1);

/*
 * (c) 2022 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Episode;
use App\Form\EpisodeType;
use App\Repository\EpisodeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\MediaBundle\Controller\AudioControllerTrait;
use Nines\MediaBundle\Controller\ImageControllerTrait;
use Nines\MediaBundle\Controller\PdfControllerTrait;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Entity\Image;
use Nines\MediaBundle\Entity\Pdf;
use Nines\MediaBundle\Service\AudioManager;
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
 * @Route("/episode")
 */
class EpisodeController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;
    use ImageControllerTrait;
    use AudioControllerTrait;
    use PdfControllerTrait;

    /**
     * @Route("/", name="episode_index", methods={"GET"})
     *
     * @Template("episode/index.html.twig")
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
     * @Template("episode/search.html.twig")
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

        foreach ($episodeRepository->typeaheadQuery($q)->execute() as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="episode_new", methods={"GET", "POST"})
     * @Template("episode/new.html.twig")
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

            foreach ($episode->getContributions() as $contribution) {
                $contribution->setEpisode($episode);
                $entityManager->persist($contribution);
            }
            $entityManager->persist($episode);
            $entityManager->flush();
            $this->addFlash('success', 'The new episode has been saved.');

            if($episode->getPodcast()->getLanguage() && ! $episode->getLanguage()) {
                $episode->setLanguage($episode->getPodcast()->getLanguage());
            }

            return $this->redirectToRoute('episode_show', ['id' => $episode->getId()]);
        }

        return [
            'episode' => $episode,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/new_popup", name="episode_new_popup", methods={"GET", "POST"})
     * @Template("episode/new_popup.html.twig")
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return array|RedirectResponse
     */
    public function new_popup(Request $request) {
        return $this->new($request);
    }

    /**
     * @Route("/{id}", name="episode_show", methods={"GET"})
     * @Template("episode/show.html.twig")
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
     * @Route("/{id}/edit", name="episode_edit", methods={"GET", "POST"})
     *
     * @Template("episode/edit.html.twig")
     *
     * @return array|RedirectResponse
     */
    public function edit(Request $request, Episode $episode) {
        $form = $this->createForm(EpisodeType::class, $episode);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();

            foreach ($episode->getContributions() as $contribution) {
                $contribution->setEpisode($episode);
                if ( ! $entityManager->contains($contribution)) {
                    $entityManager->persist($contribution);
                }
            }
            $episode->setPreserved(false);
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

    /**
     * @Route("/{id}/new_audio", name="episode_new_audio", methods={"GET", "POST"})
     * @Template("episode/new_audio.html.twig")
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @throws Exception
     *
     * @return array|RedirectResponse
     */
    public function newAudio(Request $request, EntityManagerInterface $em, Episode $episode) {
        return $this->newAudioAction($request, $em, $episode, 'episode_show');
    }

    /**
     * @Route("/{id}/edit_audio/{audio_id}", name="episode_edit_audio", methods={"GET", "POST"})
     * @Template("episode/edit_audio.html.twig")
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @ParamConverter("audio", options={"id": "audio_id"})
     *
     * @throws Exception
     *
     * @return array|RedirectResponse
     */
    public function editAudio(Request $request, EntityManagerInterface $em, Episode $episode, Audio $audio, AudioManager $fileUploader) {
        return $this->editAudioAction($request, $em, $episode, $audio, 'episode_show');
    }

    /**
     * @Route("/{id}/delete_audio/{audio_id}", name="episode_delete_audio", methods={"DELETE"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @ParamConverter("audio", options={"id": "audio_id"})
     *
     * @throws Exception
     *
     * @return RedirectResponse
     */
    public function deleteAudio(Request $request, EntityManagerInterface $em, Episode $episode, Audio $audio) {
        return $this->deleteAudioAction($request, $em, $episode, $audio, 'episode_show');
    }

    /**
     * @Route("/{id}/new_image", name="episode_new_image", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @throws Exception
     *
     * @return array<string,mixed>|RedirectResponse
     * @Template("episode/new_image.html.twig")
     */
    public function newImage(Request $request, EntityManagerInterface $em, Episode $episode) {
        return $this->newImageAction($request, $em, $episode, 'episode_show');
    }

    /**
     * @Route("/{id}/edit_image/{image_id}", name="episode_edit_image", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @ParamConverter("image", options={"id": "image_id"})
     *
     * @throws Exception
     *
     * @return array<string,mixed>|RedirectResponse
     * @Template("episode/edit_image.html.twig")
     */
    public function editImage(Request $request, EntityManagerInterface $em, Episode $episode, Image $image) {
        return $this->editImageAction($request, $em, $episode, $image, 'episode_show');
    }

    /**
     * @Route("/{id}/delete_image/{image_id}", name="episode_delete_image", methods={"DELETE"})
     * @ParamConverter("image", options={"id": "image_id"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return RedirectResponse
     */
    public function deleteImage(Request $request, EntityManagerInterface $em, Episode $episode, Image $image) {
        return $this->deleteImageAction($request, $em, $episode, $image, 'episode_show');
    }

    /**
     * @Route("/{id}/new_pdf", name="episode_new_pdf", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @Template("episode/new_pdf.html.twig")
     *
     * @return array<string,mixed>|RedirectResponse
     */
    public function newPdf(Request $request, EntityManagerInterface $em, Episode $episode) {
        return $this->newPdfAction($request, $em, $episode, 'episode_show');
    }

    /**
     * @Route("/{id}/edit_pdf/{pdf_id}", name="episode_edit_pdf", methods={"GET", "POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @ParamConverter("pdf", options={"id": "pdf_id"})
     *
     * @Template("episode/edit_pdf.html.twig")
     *
     * @return array<string,mixed>|RedirectResponse
     */
    public function editPdf(Request $request, EntityManagerInterface $em, Episode $episode, Pdf $pdf) {
        return $this->editPdfAction($request, $em, $episode, $pdf, 'episode_show');
    }

    /**
     * @Route("/{id}/delete_pdf/{pdf_id}", name="episode_delete_pdf", methods={"DELETE"})
     * @ParamConverter("pdf", options={"id": "pdf_id"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return RedirectResponse
     */
    public function deletePdf(Request $request, EntityManagerInterface $em, Episode $episode, Pdf $pdf) {
        return $this->deletePdfAction($request, $em, $episode, $pdf, 'episode_show');
    }
}
