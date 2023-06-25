<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Repository\DataRepository;
use App\Domain\Cms\Repository\GalleryRepository;
use App\Domain\Cms\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

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

    public function __invoke(PageRepository $pageRepository, DataRepository $dataRepository, GalleryRepository $galleryRepository): Response
    {
        $actualityLocked = null;
        if ($actualityLockedData = $dataRepository->findOneBy(['code' => 'INDEX_ACTUALITY_LOCKED'])?->getContent()) {
            $actualityLocked = $pageRepository->findOneBy(['slug' => $actualityLockedData[array_key_first($actualityLockedData)]]);
        }

        $actualities = $pageRepository->findBy(
            [
                'category' => Category::ACTUALITY->value,
                'status' => Status::PUBLISH,
            ],
            ['createdAt' => 'DESC'],
            $actualityLocked ? 3 : 4
        );

        if ($actualityLocked) {
            $actualities[] = $actualityLocked;
        }

        $pages = $pageRepository->findTagNameBy('sport');

        $pagesSortByTags = [];
        foreach ($pages as $page) {
            $tagsName = [];
            foreach ($page->getTags() as $tag) {
                if ($tag->getName() && 'sport' !== strtolower($tag->getName())) {
                    $tagsName[] = $tag->getName();
                }
            }

            if (!\count($tagsName)) {
                $tagsName[] = 'no-category';
            }

            if (!isset($pagesSortByTags[$tagsName[0]])) {
                $pagesSortByTags[$tagsName[0]] = [];
            }

            $pagesSortByTags[$tagsName[0]][] = $page;
        }

        return $this->render('/landing/index/index.html.twig', [
            'actualities' => $actualities,
            'header' => [
                'actualities' => $pageRepository->findBy(['category' => Category::ACTUALITY->value, 'status' => Status::PUBLISH], ['createdAt' => 'DESC'], 10),
                'galleries' => $galleryRepository->findBy([], limit: 8),
                'sport' => $pagesSortByTags,
            ],
            'contents' => $dataRepository->findOneBy(['code' => 'INDEX_PAGE_ELEMENT'])?->getContent(),
            'partners' => $dataRepository->findOneBy(['code' => 'PARTNER'])?->getContent(),
        ]);
    }
}
