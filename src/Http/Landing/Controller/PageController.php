<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Cms\Model\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    public const ROUTE_LANDING_PAGE = 'landing_page';

    #[Route('/p/{slug}', name: self::ROUTE_LANDING_PAGE)]
    public function page(Page $page): Response
    {
        return $this->render('/landing/pages/page.html.twig', [
            'page' => $page,
        ]);
    }
}
