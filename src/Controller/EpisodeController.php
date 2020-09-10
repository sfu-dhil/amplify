<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Audio;
use App\Entity\Episode;
use App\Entity\Image;
use App\Form\AudioType;
use App\Form\EpisodeType;
use App\Form\ImageType;
use App\Repository\EpisodeRepository;
use App\Services\AudioManager;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        foreach ($episodeRepository->typeaheadQuery($q) as $result) {
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
            foreach ($episode->getContributions() as $contribution) {
                $contribution->setEpisode($episode);
                $entityManager->persist($contribution);
            }
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
            $entityManager = $this->getDoctrine()->getManager();
            foreach ($episode->getContributions() as $contribution) {
                $contribution->setEpisode($episode);
                if ( ! $entityManager->contains($contribution)) {
                    $entityManager->persist($contribution);
                }
            }
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
     * @Route("/{id}/new_audio", name="episode_new_audio", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return array|RedirectResponse
     */
    public function newAudio(Request $request, Episode $episode) {
        if ($episode->getAudio()) {
            $this->addFlash('danger', 'This episode already has an audio file. Use the controls below to edit or delete the audio file.');

            return $this->redirectToRoute('episode_show', ['id' => $episode->getId()]);
        }

        $audio = new Audio();
        $audio->setEpisode($episode);
        $form = $this->createForm(AudioType::class, $audio);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($audio);
            $entityManager->flush();
            $this->addFlash('success', 'The new audio has been saved.');

            return $this->redirectToRoute('episode_show', ['id' => $episode->getId()]);
        }

        return [
            'audio' => $audio,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}/play_audio", name="episode_play_audio", methods={"GET"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return BinaryFileResponse
     */
    public function playAudio(Request $request, Episode $episode) {
        if ($episode->getAudio()) {
            return new BinaryFileResponse($episode->getAudio()->getAudioFile());
        }

        throw new NotFoundHttpException();
    }

    /**
     * @Route("/{id}/edit_audio", name="episode_edit_audio", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return array|RedirectResponse
     */
    public function editAudio(Request $request, Episode $episode, AudioManager $fileUploader) {
        if ( ! $episode->getAudio()) {
            $this->addFlash('danger', 'This episode does not have an audio file. Use the button below to add one.');

            return $this->redirectToRoute('episode_show', ['id' => $episode->getId()]);
        }

        $form = $this->createForm(AudioType::class, $episode->getAudio());
        $form->remove('audioFile');
        $form->add('newAudioFile', FileType::class, [
            'mapped' => false,
            'required' => false,
            'attr' => [
                'help_block' => "Select a file to upload which is less than {$fileUploader->getMaxUploadSize(false)} in size.",
                'data-maxsize' => $fileUploader->getMaxUploadSize(),
            ],
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (($upload = $form->get('newAudioFile')->getData())) {
                $episode->getAudio()->setAudioFile($upload);
                $episode->getAudio()->preUpdate();
            }
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();
            $this->addFlash('success', 'The new audio has been saved.');

//            return $this->redirectToRoute('episode_show', ['id' => $episode->getId()]);
        }

        return [
            'audio' => $episode->getAudio(),
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}/delete_audio", name="episode_delete_audio", methods={"DELETE"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return RedirectResponse
     */
    public function deleteAudio(Request $request, Episode $episode) {
        if ($this->isCsrfTokenValid('delete_audio_' . $episode->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($episode->getAudio());
            $entityManager->flush();
            $this->addFlash('success', 'The audio file has been deleted.');
        } else {
            $this->addFlash('warning', 'Invalid security token.');
        }

        return $this->redirectToRoute('episode_show', ['id' => $episode->getId()]);
    }

    /**
     * @Route("/{id}/new_image", name="episode_new_image", methods={"GET","POST"})
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @Template()
     */
    public function newImage(Request $request, Episode $episode) {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $image->setEntity($episode);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($image);
            $entityManager->flush();
            $this->addFlash('success', 'The new image has been saved.');

            return $this->redirectToRoute('entity_show', ['id' => $episode->getId()]);
        }

        return [
            'image' => $image,
            'form' => $form->createView(),
            'entity' => $episode,
        ];

    }

}
