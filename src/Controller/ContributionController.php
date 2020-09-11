<?php

declare(strict_types=1);

/*
 * (c) 2020 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Controller;

use App\Entity\Contribution;
use App\Repository\ContributionRepository;
use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/contribution")
 */
class ContributionController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @Route("/", name="contribution_index", methods={"GET"})
     *
     * @Template()
     */
    public function index(Request $request, ContributionRepository $contributionRepository) : array {
        $query = $contributionRepository->indexQuery();
        $pageSize = $this->getParameter('page_size');
        $page = $request->query->getint('page', 1);

        return [
            'contributions' => $this->paginator->paginate($query, $page, $pageSize),
        ];
    }

    /**
     * @Route("/{id}", name="contribution_show", methods={"GET"})
     * @Template()
     *
     * @return array
     */
    public function show(Contribution $contribution) {
        return [
            'contribution' => $contribution,
        ];
    }
}
