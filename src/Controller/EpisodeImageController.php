<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Episode;
use App\Entity\Podcast;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\MediaBundle\Controller\ImageControllerTrait;
use Nines\MediaBundle\Entity\Image;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(path: '/podcasts/{podcast_id}/episodes/{episode_id}/images', requirements: [
    'podcast_id' => Requirement::DIGITS,
    'episode_id' => Requirement::DIGITS,
])]
#[ParamConverter('podcast', options: ['id' => 'podcast_id'])]
#[ParamConverter('episode', options: ['id' => 'episode_id'])]
class EpisodeImageController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;
    use ImageControllerTrait;

    #[Route(path: '/{id}', name: 'episode_image_show', methods: ['GET'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[Template]
    public function show(Podcast $podcast, Episode $episode, Image $image) : array|RedirectResponse {
        return [
            'entity' => $episode,
            'image' => $image,
        ];
    }

    #[Route(path: '/new', name: 'episode_image_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Template]
    public function new(Request $request, EntityManagerInterface $em, Podcast $podcast, Episode $episode) : array|RedirectResponse {
        return $this->newImageAction($request, $em, $episode, 'episode_show', ['podcast_id' => $podcast->getId(), 'id' => $episode->getId()]);
    }

    #[Route(path: '/{id}/edit', name: 'episode_image_edit', methods: ['GET', 'POST'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Template]
    public function edit(Request $request, EntityManagerInterface $em, Podcast $podcast, Episode $episode, Image $image) : array|RedirectResponse {
        return $this->editImageAction($request, $em, $episode, $image, 'episode_show', ['podcast_id' => $podcast->getId(), 'id' => $episode->getId()]);
    }

    #[Route(path: '/{id}', name: 'episode_image_delete', methods: ['DELETE'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function delete(Request $request, EntityManagerInterface $em, Podcast $podcast, Episode $episode, Image $image) : RedirectResponse {
        return $this->deleteImageAction($request, $em, $episode, $image, 'episode_show', ['podcast_id' => $podcast->getId(), 'id' => $episode->getId()]);
    }
}
