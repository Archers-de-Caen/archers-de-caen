<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Repository\DataRepository;
use App\Domain\Cms\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: Request::METHOD_GET
)]
class IndexController extends AbstractController
{
    public const ROUTE = 'landing_index';

    public function __invoke(PageRepository $pageRepository, DataRepository $dataRepository): Response
    {
        $actualityLocked = null;
        if ($actualityLockedData = $dataRepository->findOneBy(['code' => 'INDEX_ACTUALITY_LOCKED'])?->getContent()) {
            $actualityLocked = $pageRepository->findOneBy(['slug' => $actualityLockedData[array_key_first($actualityLockedData)]]);
        }

        /** @var array<Page> $actualities */
        $actualities = $pageRepository->createQueryBuilder('p')
            ->select('p', 'image')
            ->leftJoin('p.image', 'image')

            ->where('p.category = :category')
            ->setParameter('category', Category::ACTUALITY->value)

            ->andWhere('p.status = :status')
            ->setParameter('status', Status::PUBLISH)

            ->orderBy('p.createdAt', 'DESC')

            ->setMaxResults($actualityLocked ? 3 : 4)

            ->getQuery()
            ->getResult()
        ;

        if ($actualityLocked) {
            $actualities[] = $actualityLocked;
        }

        return $this->render('/landing/index/index.html.twig', [
            'actualities' => $actualities,
            'contents' => $dataRepository->findOneBy(['code' => 'INDEX_PAGE_ELEMENT'])?->getContent(),
            'partners' => $dataRepository->findOneBy(['code' => 'PARTNER'])?->getContent(),
        ]);
    }
}
