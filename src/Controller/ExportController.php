<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Export;
use App\Entity\Season;
use App\Message\ExportMessage;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;

class ExportController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @return RedirectResponse
     */
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Route(path: '/season/{seasonId}/export/new', name: 'export_new', methods: ['GET', 'POST'])]
    #[ParamConverter('season', options: ['mapping' => ['seasonId' => 'id']])]
    public function new(EntityManagerInterface $entityManager, MessageBusInterface $bus, Request $request, Season $season) {
        if ($season->hasActiveExport()) {
            $this->addFlash('warning', 'There is already an ongoing export for this season.');

            return $this->redirectToRoute('season_show', ['id' => $season->getId()]);
        }

        $export = new Export();
        $export->setPendingStatus();
        $export->setMessage('Waiting to start export.');
        $export->setFormat('default');
        $season->addExport($export);

        $entityManager->persist($export);
        $entityManager->flush();

        $bus->dispatch(new ExportMessage($export->getId()));
        $this->addFlash('success', 'New exporting job created.');

        return $this->redirectToRoute('season_show', ['id' => $season->getId()]);
    }

    /**
     * @return RedirectResponse
     */
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Route(path: '/{id}', name: 'export_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Export $export) {
        $season = $export->getSeason();

        if ($this->isCsrfTokenValid('delete' . $export->getId(), $request->request->get('_token'))) {
            $entityManager->remove($export);
            $entityManager->flush();
            $this->addFlash('success', 'The export has been deleted.');
        }

        return $this->redirectToRoute('season_show', ['id' => $season->getId()]);
    }
}
