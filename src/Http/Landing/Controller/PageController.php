<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/actualites', name: 'landing_actualities')]
    public function actualities(PageRepository $pageRepository): Response
    {
        return $this->render('/landing/actualities/actualities.html.twig', [
            'actualities' => $pageRepository->findBy(
                ['category' => Category::ACTUALITY->value],
                ['createdAt' => 'DESC']
            ),
        ]);
    }

    #[Route('/{type}/{slug}', name: 'landing_page', requirements: ['type' => 'actu|p'])]
    public function page(Page $page): Response
    {
        return $this->render('/landing/pages/page.html.twig', [
            'page' => $page,
        ]);
    }
}
