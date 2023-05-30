<?php

declare(strict_types=1);

namespace App\Controller;

use Knp\Bundle\PaginatorBundle\Definition\PaginatorAwareInterface;
use Nines\UtilBundle\Controller\PaginatorTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController implements PaginatorAwareInterface {
    use PaginatorTrait;

    /**
     * @return array
     */
    #[Route(path: '/', name: 'homepage')]
    #[Template]
    public function indexAction(Request $request) : void {
    }

    #[Route(path: '/privacy', name: 'privacy')]
    #[Template]
    public function privacyAction(Request $request) : void {
    }
}
