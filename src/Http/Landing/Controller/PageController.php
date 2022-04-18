<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Cms\Model\Page;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/p/{slug}', name: 'landing_page')]
    public function page(Page $page): Response
    {
        return $this->render('/landing/pages/page.html.twig', [
            'page' => $page,
        ]);
    }
}
