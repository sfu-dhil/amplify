<?php

namespace App\Controller;

use App\Entity\Language;
use App\Form\LanguageType;
use App\Repository\LanguageRepository;

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
 * @Route("/language")
 * @IsGranted("ROLE_USER")
 */
class LanguageController extends AbstractController implements PaginatorAwareInterface
{
    use PaginatorTrait;

    /**
     * @Route("/", name="language_index", methods={"GET"})
     * @param Request $request
     * @param LanguageRepository $languageRepository
     *
     * @Template()
     *
     * @return array
     */
    public function index(Request $request, LanguageRepository $languageRepository) : array
    {
        $query = $languageRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'languages' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="language_search", methods={"GET"})
     *
     * @Template()
     *
     * @return array
     */
    public function search(Request $request, LanguageRepository $languageRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $languageRepository->searchQuery($q);
            $languages = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), array('wrap-queries'=>true));
        } else {
            $languages = [];
        }

        return [
            'languages' => $languages,
            'q' => $q,
        ];
    }

    /**
     * @Route("/typeahead", name="language_typeahead", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request, LanguageRepository $languageRepository) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($languageRepository->typeaheadSearch($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string)$result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="language_new", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function new(Request $request) {
        $language = new Language();
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($language);
            $entityManager->flush();
            $this->addFlash('success', 'The new language has been saved.');

            return $this->redirectToRoute('language_show', ['id' => $language->getId()]);
        }

        return [
            'language' => $language,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/new_popup", name="language_new_popup", methods={"GET","POST"})
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
     * @Route("/{id}", name="language_show", methods={"GET"})
     * @Template()
     * @param Language $language
     *
     * @return array
     */
    public function show(Language $language) {
        return [
            'language' => $language,
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="language_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Language $language
     *
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function edit(Request $request, Language $language) {
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The updated language has been saved.');

            return $this->redirectToRoute('language_show', ['id' => $language->getId()]);
        }

        return [
            'language' => $language,
            'form' => $form->createView()
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="language_delete", methods={"DELETE"})
     * @param Request $request
     * @param Language $language
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Language $language) {
        if ($this->isCsrfTokenValid('delete' . $language->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($language);
            $entityManager->flush();
            $this->addFlash('success', 'The language has been deleted.');
        }

        return $this->redirectToRoute('language_index');
    }
}
