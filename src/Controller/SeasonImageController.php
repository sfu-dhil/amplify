<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Podcast;
use App\Entity\Season;
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

#[Route(path: '/podcasts/{podcast_id}/seasons/{season_id}/images', requirements: [
    'podcast_id' => Requirement::DIGITS,
    'season_id' => Requirement::DIGITS,
])]
#[ParamConverter('podcast', options: ['id' => 'podcast_id'])]
#[ParamConverter('season', options: ['id' => 'season_id'])]
class SeasonImageController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;
    use ImageControllerTrait;

    #[Route(path: '/{id}', name: 'season_image_show', methods: ['GET'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[Template]
    public function show(Podcast $podcast, Season $season, Image $image) : array|RedirectResponse {
        return [
            'entity' => $season,
            'image' => $image,
        ];
    }

    #[Route(path: '/new', name: 'season_image_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Template]
    public function new(Request $request, EntityManagerInterface $em, Podcast $podcast, Season $season) : array|RedirectResponse {
        return $this->newImageAction($request, $em, $season, 'season_show', ['podcast_id' => $podcast->getId(), 'id' => $season->getId()]);
    }

    #[Route(path: '/{id}/edit', name: 'season_image_edit', methods: ['GET', 'POST'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Template]
    public function edit(Request $request, EntityManagerInterface $em, Podcast $podcast, Season $season, Image $image) : array|RedirectResponse {
        return $this->editImageAction($request, $em, $season, $image, 'season_show', ['podcast_id' => $podcast->getId(), 'id' => $season->getId()]);
    }

    #[Route(path: '/{id}', name: 'season_image_delete', methods: ['DELETE'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function delete(Request $request, EntityManagerInterface $em, Podcast $podcast, Season $season, Image $image) : RedirectResponse {
        return $this->deleteImageAction($request, $em, $season, $image, 'season_show', ['podcast_id' => $podcast->getId(), 'id' => $season->getId()]);
    }
}
