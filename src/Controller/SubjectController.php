<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Subject;
use App\Form\SubjectType;
use App\Repository\SubjectRepository;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/subject")
 */
class SubjectController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="subject_index", methods={"GET"})
     *
     * @Template()
     */
    public function index(Request $request, SubjectRepository $subjectRepository) : array {
        $query = $subjectRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'subjects' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="subject_search", methods={"GET"})
     *
     * @Template()
     *
     * @return array
     */
    public function search(Request $request, SubjectRepository $subjectRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $subjectRepository->searchQuery($q);
            $subjects = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $subjects = [];
        }

        return [
            'subjects' => $subjects,
            'q' => $q,
        ];
    }

    /**
     * @Route("/typeahead", name="subject_typeahead", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request, SubjectRepository $subjectRepository) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($subjectRepository->typeaheadSearch($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="subject_new", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return array|RedirectResponse
     */
    public function new(Request $request) {
        $subject = new Subject();
        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($subject);
            $entityManager->flush();
            $this->addFlash('success', 'The new subject has been saved.');

            return $this->redirectToRoute('subject_show', ['id' => $subject->getId()]);
        }

        return [
            'subject' => $subject,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/new_popup", name="subject_new_popup", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     *
     * @return array|RedirectResponse
     */
    public function new_popup(Request $request) {
        return $this->new($request);
    }

    /**
     * @Route("/{id}", name="subject_show", methods={"GET"})
     * @Template()
     *
     * @return array
     */
    public function show(Subject $subject) {
        return [
            'subject' => $subject,
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="subject_edit", methods={"GET","POST"})
     *
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function edit(Request $request, Subject $subject) {
        $form = $this->createForm(SubjectType::class, $subject);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The updated subject has been saved.');

            return $this->redirectToRoute('subject_show', ['id' => $subject->getId()]);
        }

        return [
            'subject' => $subject,
            'form' => $form->createView(),
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="subject_delete", methods={"DELETE"})
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Subject $subject) {
        if ($this->isCsrfTokenValid('delete' . $subject->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($subject);
            $entityManager->flush();
            $this->addFlash('success', 'The subject has been deleted.');
        }

        return $this->redirectToRoute('subject_index');
    }
}
