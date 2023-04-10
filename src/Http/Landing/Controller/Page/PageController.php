<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Page;

use App\Domain\Cms\Model\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/p/{slug}',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class PageController extends AbstractController
{
    public const ROUTE = 'landing_page';

    public function __invoke(Page $page): Response
    {
        return $this->render('/landing/pages/page.html.twig', [
            'page' => $page,
        ]);
    }
}
