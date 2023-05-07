<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Actuality;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
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
    path: '/actualites',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: Request::METHOD_GET
)]
class ActualitiesController extends AbstractController
{
    public const ROUTE = 'landing_actualities';

    public function __invoke(Request $request, PageRepository $pageRepository): Response
    {
        $currentPage = ((int) $request->query->get('page') ?: 1) - 1;
        $elementByPage = 24;

        $actualities = new Paginator(
            $pageRepository
                ->createQueryBuilder('p')
                ->where('p.category = :category')
                ->andWhere('p.status = :status')
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
}
