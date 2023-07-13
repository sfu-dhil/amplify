<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Import;
use App\Entity\Podcast;
use App\Form\ImportType;
use App\Message\ImportMessage;
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
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

#[Route(path: '/podcasts')]
#[IsGranted('ROLE_USER')]
class ImportController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Template]
    #[Route(path: '/imports/new', name: 'import_new', methods: ['GET', 'POST'])]
    public function new(EntityManagerInterface $entityManager, MessageBusInterface $bus, Request $request) : array|RedirectResponse {
        $import = new Import();
        $import->setUser($this->getUser());
        $form = $this->createForm(ImportType::class, $import);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $import->setPendingStatus();
            $import->setMessage('Waiting to start import.');

            $entityManager->persist($import);
            $entityManager->flush();

            $bus->dispatch(new ImportMessage($import->getId()));
            $this->addFlash('success', 'New import job created.');

            return $this->redirectToRoute('import_show', ['id' => $import->getId()]);
        }

        return [
            'import' => $import,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{podcast_id}/imports/new', name: 'podcast_import_new', methods: ['GET', 'POST'], requirements: [
        'podcast_id' => Requirement::DIGITS,
    ])]
    #[ParamConverter('podcast', options: ['id' => 'podcast_id'])]
    #[IsGranted('access', 'podcast')]
    public function podcast_new(EntityManagerInterface $entityManager, MessageBusInterface $bus, Request $request, Podcast $podcast) : RedirectResponse {
        $import = new Import();
        $import->setUser($this->getUser());
        $import->setRss($podcast->getRss());
        $import->setPendingStatus();
        $import->setMessage('Waiting to start import.');
        $podcast->addImport($import);

        $entityManager->persist($import);
        $entityManager->flush();

        $bus->dispatch(new ImportMessage($import->getId()));
        $this->addFlash('success', 'New import job created.');

        return $this->redirectToRoute('import_show', ['id' => $import->getId()]);
    }

    #[Route(path: '/imports/{id}', name: 'import_show', methods: ['GET'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    #[Template]
    public function show(Import $import) : array|RedirectResponse {
        return [
            'import' => $import,
        ];
    }

    #[Route(path: '/imports/{id}/details', name: 'import_show_details_json', methods: ['GET'], requirements: [
        'id' => Requirement::DIGITS,
    ])]
    public function show_details_json(Import $import) : JsonResponse {
        return new JsonResponse([
            'content' => $this->renderView('import/partial/details.html.twig', [
                'import' => $import,
            ]),
            'isActive' => $import->isActive(),
        ]);
    }
}
