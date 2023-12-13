<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Podcast;
use App\Entity\Share;
use App\Form\ShareType;
use App\Repository\ShareRepository;
use App\Repository\UserRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Requirement\Requirement;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[Route(path: '/podcasts/{podcast_id}/shares', requirements: [
    'podcast_id' => Requirement::DIGITS,
])]
#[ParamConverter('podcast', options: ['id' => 'podcast_id'])]
#[IsGranted('access', 'podcast')]
class ShareController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Route(path: '', name: 'share_index', methods: ['GET', 'POST'])]
    #[Template]
    public function index(EntityManagerInterface $entityManager, Request $request, ShareRepository $shareRepository, Podcast $podcast) : array|RedirectResponse {
        $share = new Share();
        $share->setPodcast($podcast);
        $podcast->addShare($share);

        $form = $this->createForm(ShareType::class, $share);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $entityManager->persist($share);
                $entityManager->flush();
                $this->addFlash('success', 'Successfully shared podcast.');
            } catch (UniqueConstraintViolationException $e) {
                $this->addFlash('warning', "{$share->getUser()->getFullname()} already has access to the podcast.");
            }

            return $this->redirectToRoute('share_index', ['podcast_id' => $podcast->getId()]);
        }

        return [
            'podcast' => $podcast,
            'shares' => $shareRepository->indexQuery($podcast)->getResult(),
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'share_delete', methods: ['DELETE'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Podcast $podcast, Share $share) : RedirectResponse {
        if ($share->getPodcast() !== $podcast) {
            throw new ResourceNotFoundException();
        }

        $user = $share->getUser();

        if ($user === $this->getUser() && ! $this->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException('You cannot remove yourself from the podcast.');
        }

        if ($this->isCsrfTokenValid('delete_share' . $share->getId(), $request->request->get('_token'))) {
            $entityManager->remove($share);
            $entityManager->flush();
            $this->addFlash('success', "Removed access for {$user->getFullname()}");
        }

        return $this->redirectToRoute('share_index', ['podcast_id' => $podcast->getId()]);
    }

    #[Route(path: '/typeahead', name: 'share_user_typeahead', methods: ['GET'])]
    public function typeahead(Request $request, UserRepository $userRepository, Podcast $podcast) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            $q = '%';
        }
        $data = [];

        foreach ($userRepository->typeaheadQuery($q)->execute() as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }
}
