<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Season;
use App\Entity\Export;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;

use Symfony\Component\Messenger\MessageBusInterface;
use App\Message\ExportMessage;

class ExportController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/season/{seasonId}/export/new", name="export_new", methods={"GET", "POST"})
     *
     * @ParamConverter("season", options={"mapping": {"seasonId" : "id"}})
     *
     * @return RedirectResponse
     */
    public function new(MessageBusInterface $bus, Request $request, Season $season) {
        if ($season->hasActiveExport()) {
            $this->addFlash('warning', 'There is already an ongoing export for this season.');
            return $this->redirectToRoute('season_show', ['id' => $season->getId()]);
        }

        $entityManager = $this->getDoctrine()->getManager();

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
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="export_delete", methods={"DELETE"})
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Export $export) {
        $season = $export->getSeason();

        if ($this->isCsrfTokenValid('delete' . $export->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($export);
            $entityManager->flush();
            $this->addFlash('success', 'The export has been deleted.');
        }

        return $this->redirectToRoute('season_show', ['id' => $season->getId()]);
    }
}
