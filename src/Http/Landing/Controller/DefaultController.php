<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Repository\PageRepository;
use App\Helper\PaginatorHelper;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'landing_index')]
    public function index(PageRepository $pageRepository): Response
    {
        return $this->render('/landing/index/index.html.twig', [
            'actualities' => $pageRepository->findBy(
                ['category' => Category::ACTUALITY->value],
                ['createdAt' => 'DESC'],
                4
            ),
        ]);
    }

    #[Route('/contact', name: 'landing_contact')]
    public function contact(): Response
    {
        return $this->render('/landing/contact/contact.html.twig');
    }

    #[Route('/style-guide', name: 'landing_style_guide')]
    public function styleGuide(Request $request): Response
    {
        return $this->render('/landing/style-guide/style-guide.html.twig', [
            'paginator' => PaginatorHelper::pagination((int) ($request->query->get('page') ?: 1), 100)
        ]);
    }
}
