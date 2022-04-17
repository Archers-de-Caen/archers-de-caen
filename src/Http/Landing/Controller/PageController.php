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
use Symfony\Component\Routing\Annotation\Route;

class PageController extends AbstractController
{
    #[Route('/actualites', name: 'landing_actualities')]
    public function actualities(Request $request, PageRepository $pageRepository): Response
    {
        $currentPage = (int) $request->query->get('page') ?: 0;
        $elementByPage = 24;

        $actualities = new Paginator(
            $pageRepository
                ->createQueryBuilder('p')
                ->where("p.category = '" . Category::ACTUALITY->value . "'")
                ->andWhere("p.status = '" . Status::PUBLISH->value . "'")
                ->orderBy('p.createdAt', 'DESC')
                ->setFirstResult($currentPage)
                ->setMaxResults((int) ($elementByPage * ($request->query->get('page') ?: 1))) // 24, car sur un écran 1080p la dernière ligne est complete
        );

        PaginatorHelper::pagination(5, 100);

        return $this->render('/landing/actualities/actualities.html.twig', [
            'actualities' => $actualities,
            'paginator' => PaginatorHelper::pagination($currentPage, (int) ceil($elementByPage / $actualities->count()) + 1),
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
