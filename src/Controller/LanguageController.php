<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Language;
use App\Form\LanguageType;
use App\Repository\LanguageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/languages')]
class LanguageController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    #[Route(path: '', name: 'language_index', methods: ['GET'])]
    #[Template]
    public function index(Request $request, LanguageRepository $languageRepository) : array {
        $q = $request->query->get('q');
        $query = $q ? $languageRepository->searchQuery($q) : $languageRepository->indexQuery();

        return [
            'languages' => $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]),
            'q' => $q,
        ];
    }

    #[Route(path: '/typeahead', name: 'language_typeahead', methods: ['GET'])]
    public function typeahead(Request $request, LanguageRepository $languageRepository) : JsonResponse {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];

        foreach ($languageRepository->typeaheadQuery($q)->execute() as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string) $result,
            ];
        }

        return new JsonResponse($data);
    }

    #[Route(path: '/new', name: 'language_new', methods: ['GET', 'POST'])]
    #[Template]
    #[IsGranted('ROLE_CONTENT_ADMIN')]
    public function new(EntityManagerInterface $entityManager, Request $request) : array|RedirectResponse {
        $language = new Language();
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($language);
            $entityManager->flush();
            $this->addFlash('success', 'Language created successfully.');

            return $this->redirectToRoute('language_show', ['id' => $language->getId()]);
        }

        return [
            'language' => $language,
            'form' => $form->createView(),
        ];
    }

    #[Route(path: '/{id}', name: 'language_show', methods: ['GET'])]
    #[Template]
    public function show(Language $language) : array {
        return [
            'language' => $language,
        ];
    }

    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Route(path: '/{id}/edit', name: 'language_edit', methods: ['GET', 'POST'])]
    #[Template]
    public function edit(EntityManagerInterface $entityManager, Request $request, Language $language) : array|RedirectResponse {
        $form = $this->createForm(LanguageType::class, $language);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $this->addFlash('success', 'Language updated successfully.');

            return $this->redirectToRoute('language_show', ['id' => $language->getId()]);
        }

        return [
            'language' => $language,
            'form' => $form->createView(),
        ];
    }

    #[IsGranted('ROLE_CONTENT_ADMIN')]
    #[Route(path: '/{id}', name: 'language_delete', methods: ['DELETE'])]
    public function delete(EntityManagerInterface $entityManager, Request $request, Language $language) : RedirectResponse {
        if ($this->isCsrfTokenValid('delete_language' . $language->getId(), $request->request->get('_token'))) {
            $entityManager->remove($language);
            $entityManager->flush();
            $this->addFlash('success', 'The language has been deleted.');
        }

        return $this->redirectToRoute('language_index');
    }
}
