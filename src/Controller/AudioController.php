<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Audio;
use App\Repository\AudioRepository;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/audio")
 * @IsGranted("ROLE_USER")
 */
class AudioController extends AbstractController implements PaginatorAwareInterface
{
    use PaginatorTrait;

    /**
     * @Route("/", name="audio_index", methods={"GET"})
     *
     * @Template
     */
    public function index(Request $request, AudioRepository $audioRepository) : array {
        $query = $audioRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'audio' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/search", name="audio_search", methods={"GET"})
     *
     * @Template
     *
     * @return array
     */
    public function search(Request $request, AudioRepository $audioRepository) {
        $q = $request->query->get('q');
        if ($q) {
            $query = $audioRepository->searchQuery($q);
            $audio = $this->paginator->paginate($query, $request->query->getInt('page', 1), $this->getParameter('page_size'), ['wrap-queries' => true]);
        } else {
            $audio = [];
        }

        return [
            'audio' => $audio,
            'q' => $q,
        ];
    }

    /**
     * @Route("/{id}", name="audio_show", methods={"GET"})
     * @Template
     *
     * @return array
     */
    public function show(Audio $audio) {
        return [
            'audio' => $audio,
        ];
    }
}
