<?php

namespace App\Controller;

use App\Entity\Publisher;
use App\Form\PublisherType;
use App\Repository\PublisherRepository;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/publisher")
 * @IsGranted("ROLE_USER")
 */
class PublisherController extends AbstractController implements PaginatorAwareInterface
{
    use PaginatorTrait;

    /**
     * @Route("/", name="publisher_index", methods={"GET"})
     * @param Request $request
     * @param PublisherRepository $publisherRepository
     *
     * @Template()
     *
     * @return array
     */
    public function index(Request $request, PublisherRepository $publisherRepository) : array
    {
        $query = $publisherRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'publishers' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="publisher_search", methods={"GET"})
     *
     * @Template()
     *
     * @return array
     */
    public function search(Request $request, PublisherRepository $publisherRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $publisherRepository->searchQuery($q);
            $publishers = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), array('wrap-queries'=>true));
        } else {
            $publishers = [];
        }

        return [
            'publishers' => $publishers,
            'q' => $q,
        ];
    }

    /**
     * @Route("/typeahead", name="publisher_typeahead", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request, PublisherRepository $publisherRepository) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($publisherRepository->typeaheadSearch($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string)$result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="publisher_new", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function new(Request $request) {
        $publisher = new Publisher();
        $form = $this->createForm(PublisherType::class, $publisher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($publisher);
            $entityManager->flush();
            $this->addFlash('success', 'The new publisher has been saved.');

            return $this->redirectToRoute('publisher_show', ['id' => $publisher->getId()]);
        }

        return [
            'publisher' => $publisher,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/new_popup", name="publisher_new_popup", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function new_popup(Request $request) {
        return $this->new($request);
    }

    /**
     * @Route("/{id}", name="publisher_show", methods={"GET"})
     * @Template()
     * @param Publisher $publisher
     *
     * @return array
     */
    public function show(Publisher $publisher) {
        return [
            'publisher' => $publisher,
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="publisher_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Publisher $publisher
     *
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function edit(Request $request, Publisher $publisher) {
        $form = $this->createForm(PublisherType::class, $publisher);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The updated publisher has been saved.');

            return $this->redirectToRoute('publisher_show', ['id' => $publisher->getId()]);
        }

        return [
            'publisher' => $publisher,
            'form' => $form->createView()
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="publisher_delete", methods={"DELETE"})
     * @param Request $request
     * @param Publisher $publisher
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Publisher $publisher) {
        if ($this->isCsrfTokenValid('delete' . $publisher->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($publisher);
            $entityManager->flush();
            $this->addFlash('success', 'The publisher has been deleted.');
        }

        return $this->redirectToRoute('publisher_index');
    }
}
