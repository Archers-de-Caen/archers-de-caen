<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class PageController extends AbstractController
{
    public const ROUTE_LANDING_PAGE = 'landing_page';
    public const ROUTE_LANDING_PAGE_SPORT = 'landing_page_sport';

    #[Route('/p/{slug}', name: self::ROUTE_LANDING_PAGE)]
    public function page(Page $page): Response
    {
        return $this->render('/landing/pages/page.html.twig', [
            'page' => $page,
        ]);
    }

    #[Route('/le-sport', name: self::ROUTE_LANDING_PAGE_SPORT)]
    public function sport(PageRepository $pageRepository): Response
    {
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

        return $this->render('/landing/pages/sport.html.twig', [
            'pagesSortByTags' => $pagesSortByTags,
        ]);
    }
}
