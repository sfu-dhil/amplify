<?php

namespace App\Controller;

use App\Entity\Category;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;

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
 * @Route("/category")
 * @IsGranted("ROLE_USER")
 */
class CategoryController extends AbstractController implements PaginatorAwareInterface
{
    use PaginatorTrait;

    /**
     * @Route("/", name="category_index", methods={"GET"})
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     *
     * @Template()
     *
     * @return array
     */
    public function index(Request $request, CategoryRepository $categoryRepository) : array
    {
        $query = $categoryRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'categories' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="category_search", methods={"GET"})
     *
     * @Template()
     *
     * @return array
     */
    public function search(Request $request, CategoryRepository $categoryRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $categoryRepository->searchQuery($q);
            $categories = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), array('wrap-queries'=>true));
        } else {
            $categories = [];
        }

        return [
            'categories' => $categories,
            'q' => $q,
        ];
    }

    /**
     * @Route("/typeahead", name="category_typeahead", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request, CategoryRepository $categoryRepository) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($categoryRepository->typeaheadSearch($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string)$result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="category_new", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function new(Request $request) {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($category);
            $entityManager->flush();
            $this->addFlash('success', 'The new category has been saved.');

            return $this->redirectToRoute('category_show', ['id' => $category->getId()]);
        }

        return [
            'category' => $category,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/new_popup", name="category_new_popup", methods={"GET","POST"})
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
     * @Route("/{id}", name="category_show", methods={"GET"})
     * @Template()
     * @param Category $category
     *
     * @return array
     */
    public function show(Category $category) {
        return [
            'category' => $category,
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="category_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Category $category
     *
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function edit(Request $request, Category $category) {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The updated category has been saved.');

            return $this->redirectToRoute('category_show', ['id' => $category->getId()]);
        }

        return [
            'category' => $category,
            'form' => $form->createView()
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="category_delete", methods={"DELETE"})
     * @param Request $request
     * @param Category $category
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Category $category) {
        if ($this->isCsrfTokenValid('delete' . $category->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($category);
            $entityManager->flush();
            $this->addFlash('success', 'The category has been deleted.');
        }

        return $this->redirectToRoute('category_index');
    }
}
