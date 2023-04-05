<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Podcast;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\MediaBundle\Controller\AudioControllerTrait;
use Nines\MediaBundle\Entity\Audio;
use Nines\MediaBundle\Service\AudioManager;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(path: '/podcasts/{podcast_id}/episodes/{episode_id}/audio', requirements: [
    'podcast_id' => Requirement::DIGITS,
    'episode_id' => Requirement::DIGITS,
])]
#[ParamConverter('podcast', options: ['id' => 'podcast_id'])]
#[ParamConverter('episode', options: ['id' => 'episode_id'])]
class EpisodeAudioController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;
    use AudioControllerTrait;

    #[Route(path: '/{id}', name: 'episode_audio_show', methods: ['GET'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[Template]
    public function show(Podcast $podcast, Episode $episode, Audio $audio) : array|RedirectResponse {
        return [
            'entity' => $episode,
            'audio' => $audio,
        ];
    }

    #[Route(path: '/new', name: 'episode_audio_new', methods: ['GET', 'POST'])]
    #[Template]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function new(Request $request, EntityManagerInterface $em, Podcast $podcast, Episode $episode) : array|RedirectResponse {
        return $this->newAudioAction($request, $em, $episode, 'episode_show', ['podcast_id' => $podcast->getId(), 'id' => $episode->getId()]);
    }

    #[Route(path: '/{id}/edit', name: 'episode_audio_edit', methods: ['GET', 'POST'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[Template]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function edit(Request $request, EntityManagerInterface $em, Podcast $podcast, Episode $episode, Audio $audio, AudioManager $fileUploader) : array|RedirectResponse {
        return $this->editAudioAction($request, $em, $episode, $audio, 'episode_show', ['podcast_id' => $podcast->getId(), 'id' => $episode->getId()]);
    }

    #[Route(path: '/{id}', name: 'episode_audio_delete', methods: ['DELETE'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function delete(Request $request, EntityManagerInterface $em, Podcast $podcast, Episode $episode, Audio $audio) : RedirectResponse {
        return $this->deleteAudioAction($request, $em, $episode, $audio, 'episode_show', ['podcast_id' => $podcast->getId(), 'id' => $episode->getId()]);
    }
}
