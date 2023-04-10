<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Repository\PageRepository;
use App\Helper\PaginatorHelper;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/boutique',
    name: ShopController::ROUTE,
    methods: [Request::METHOD_GET]
)]
class ShopController extends AbstractController
{
    public const ROUTE = 'landing_shop';

    public function __invoke(): Response
    {
        return $this->render('/landing/shop/index.html.twig');
    }
}
