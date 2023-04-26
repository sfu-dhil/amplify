<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Export;
use App\Entity\Podcast;
use App\Form\ExportType;
use App\Message\ExportMessage;
use App\Repository\ExportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(path: '/podcasts/{podcast_id}/exports', requirements: [
    'podcast_id' => Requirement::DIGITS,
])]
#[ParamConverter('podcast', options: ['id' => 'podcast_id'])]
class ExportController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Route(path: '', name: 'export_index', methods: ['GET'])]
    #[Template]
    public function index(Request $request, Podcast $podcast, ExportRepository $exportRepository) : array|RedirectResponse {
        $q = $request->query->get('q');
        $query = $q ? $exportRepository->searchQuery($podcast, $q) : $exportRepository->indexQuery($podcast);

        return [
            'podcast' => $podcast,
            'exports' => $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]),
            'q' => $q,
        ];
    }

    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Template]
    #[Route(path: '/new', name: 'export_new', methods: ['GET', 'POST'])]
    public function new(EntityManagerInterface $entityManager, MessageBusInterface $bus, Request $request, Podcast $podcast) : array|RedirectResponse {
        $export = new Export();
        $podcast->addExport($export);

        $form = $this->createForm(ExportType::class, $export);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $export->setPendingStatus();
            $export->setMessage('Waiting to start export.');

            $entityManager->persist($export);
            $entityManager->flush();

            $bus->dispatch(new ExportMessage($export->getId()));
            $this->addFlash('success', 'New export job created.');

            return $this->redirectToRoute('export_show', ['podcast_id' => $podcast->getId(), 'id' => $export->getId()]);
        }

        return [
            'export' => $export,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'export_show', methods: ['GET'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[Template]
    public function show(Podcast $podcast, Export $export) : array|RedirectResponse {
        return [
            'export' => $export,
        ];
    }

    #[Route(path: '/{id}/details', name: 'export_show_details_json', methods: ['GET'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    public function show_details_json(Podcast $podcast, Export $export) : JsonResponse {
        return new JsonResponse([
            'content' => $this->renderView('export/partial/details.html.twig', [
                'export' => $export,
            ]),
            'isActive' => $export->isActive(),
        ]);
    }

    #[Route(path: '/{id}/download', name: 'export_download', methods: ['GET'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    public function download(Podcast $podcast, Export $export) : BinaryFileResponse {
        $fullPath = $this->getParameter('export_root_dir') . '/' . $export->getPath();
        $response = new BinaryFileResponse($fullPath);
        $response->trustXSendfileTypeHeader();
        $response->headers->set('Content-Type', 'application/zip');

        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            "{$podcast->getTitle()} - {$export->getFormat()}.zip"
        );

        return $response;
    }

    /**
     * @return RedirectResponse
     */
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Route(path: '/{id}', name: 'export_delete', methods: ['DELETE'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Filesystem $filesystem, Podcast $podcast, Export $export) {
        $podcast = $export->getPodcast();
        $relativePath = $export->getPath();
        $fullPath = $this->getParameter('export_root_dir') . "/{$relativePath}";

        if ($this->isCsrfTokenValid('delete_export' . $export->getId(), $request->request->get('_token'))) {
            $entityManager->remove($export);
            $entityManager->flush();
            // cleanup the zip file
            if ($relativePath && $filesystem->exists($fullPath)) {
                $filesystem->remove($fullPath);
            }
            $this->addFlash('success', 'The export has been deleted.');
        }

        return $this->redirectToRoute('export_index', ['podcast_id' => $podcast->getId()]);
    }
}
