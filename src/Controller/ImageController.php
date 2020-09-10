<?php

namespace App\Controller;

use App\Entity\Image;
use App\Form\ImageType;
use App\Repository\ImageRepository;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * @Route("/image")
 */
class ImageController extends AbstractController implements PaginatorAwareInterface
{
    use PaginatorTrait;

    /**
     * @Route("/", name="image_index", methods={"GET"})
     * @param Request $request
     * @param ImageRepository $imageRepository
     *
     * @Template()
     *
     * @return array
     */
    public function index(Request $request, ImageRepository $imageRepository) : array
    {
        $query = $imageRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'images' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="image_search", methods={"GET"})
     *
     * @Template()
     *
     * @return array
     */
    public function search(Request $request, ImageRepository $imageRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $imageRepository->searchQuery($q);
            $images = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), array('wrap-queries'=>true));
        } else {
            $images = [];
        }

        return [
            'images' => $images,
            'q' => $q,
        ];
    }

    /**
     * @Route("/typeahead", name="image_typeahead", methods={"GET"})
     *
     * @return JsonResponse
     */
    public function typeahead(Request $request, ImageRepository $imageRepository) {
        $q = $request->query->get('q');
        if ( ! $q) {
            return new JsonResponse([]);
        }
        $data = [];
        foreach ($imageRepository->typeaheadQuery($q) as $result) {
            $data[] = [
                'id' => $result->getId(),
                'text' => (string)$result,
            ];
        }

        return new JsonResponse($data);
    }

    /**
     * @Route("/new", name="image_new", methods={"GET","POST"})
     * @Template()
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @param Request $request
     *
     * @return array|RedirectResponse
     */
    public function new(Request $request) {
        $image = new Image();
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($image);
            $entityManager->flush();
            $this->addFlash('success', 'The new image has been saved.');

            return $this->redirectToRoute('image_show', ['id' => $image->getId()]);
        }

        return [
            'image' => $image,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/new_popup", name="image_new_popup", methods={"GET","POST"})
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
     * @Route("/{id}", name="image_show", methods={"GET"})
     * @Template()
     * @param Image $image
     *
     * @return array
     */
    public function show(Image $image) {
        return [
            'image' => $image,
        ];
    }

    /**
     * @Route("/{id}/view", name="image_view", methods={"GET"})
     * @param Image $image
     *
     * @return BinaryFileResponse
     */
    public function view(Image $image) {
        if ( ! $image->getPublic() && ! $this->getUser()) {
            throw new AccessDeniedHttpException();
        }
        return new BinaryFileResponse($image->getImageFile());
    }

    /**
     * @Route("/{id}/thumb", name="image_thumb", methods={"GET"})
     * @param Image $image
     *
     * @return BinaryFileResponse
     */
    public function thumbnail(Image $image) {
        if ( ! $image->getPublic() && ! $this->getUser()) {
            throw new AccessDeniedHttpException();
        }

        return new BinaryFileResponse($image->getThumbFile());
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}/edit", name="image_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Image $image
     *
     * @Template()
     *
     * @return array|RedirectResponse
     */
    public function edit(Request $request, Image $image) {
        $form = $this->createForm(ImageType::class, $image);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'The updated image has been saved.');

            return $this->redirectToRoute('image_show', ['id' => $image->getId()]);
        }

        return [
            'image' => $image,
            'form' => $form->createView()
        ];
    }

    /**
     * @IsGranted("ROLE_CONTENT_ADMIN")
     * @Route("/{id}", name="image_delete", methods={"DELETE"})
     * @param Request $request
     * @param Image $image
     *
     * @return RedirectResponse
     */
    public function delete(Request $request, Image $image) {
        if ($this->isCsrfTokenValid('delete' . $image->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($image);
            $entityManager->flush();
            $this->addFlash('success', 'The image has been deleted.');
        }

        return $this->redirectToRoute('image_index');
    }
}
