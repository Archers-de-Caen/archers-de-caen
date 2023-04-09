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

class ActualitiesController extends AbstractController
{
    public const ROUTE_LANDING_ACTUALITIES = 'landing_actualities';
    public const ROUTE_LANDING_ACTUALITY = 'landing_actuality';

    #[Route('/actualites', name: self::ROUTE_LANDING_ACTUALITIES)]
    public function actualities(Request $request, PageRepository $pageRepository): Response
    {
        $currentPage = ((int) $request->query->get('page') ?: 1) - 1;
        $elementByPage = 24;

        $actualities = new Paginator(
            $pageRepository
                ->createQueryBuilder('p')
                ->where("p.category = :category")
                ->andWhere("p.status = :status")
                ->setParameter('status', Status::PUBLISH->value)
                ->setParameter('category', Category::ACTUALITY->value)
                ->orderBy('p.createdAt', 'DESC')
                ->setFirstResult($currentPage * $elementByPage)
                ->setMaxResults(24) // 24, car sur un écran 1080p la dernière ligne est complete
        );

        return $this->render('/landing/actualities/actualities.html.twig', [
            'actualities' => $actualities,
            'paginator' => PaginatorHelper::pagination($currentPage + 1, (int) ceil($actualities->count() / $elementByPage)),
        ]);
    }

    #[Route('/actualite/{slug}', name: self::ROUTE_LANDING_ACTUALITY)]
    public function page(Page $actuality, PageRepository $pageRepository): Response
    {
        $pages = $pageRepository
            ->findBy([
                'category' => Category::ACTUALITY->value,
                'status' => Status::PUBLISH->value,
            ], ['createdAt' => 'DESC']);

        foreach ($pages as $key => $page) {
            if ($page->getId() === $actuality->getId()) {
                $nextPage = $pages[$key + 1] ?? null;

                break;
            }
        }

        return $this->render('/landing/actualities/actuality.html.twig', [
            'page' => $actuality,
            'nextPage' => $nextPage ?? null,
        ]);
    }
}
