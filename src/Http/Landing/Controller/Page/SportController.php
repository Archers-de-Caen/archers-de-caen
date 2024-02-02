<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Page;

use App\Domain\Cms\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/le-sport',
    name: self::ROUTE,
    options: ['sitemap' => true],
    methods: Request::METHOD_GET
)]
final class SportController extends AbstractController
{
    public const string ROUTE = 'landing_page_sport';

    public function __invoke(PageRepository $pageRepository): Response
    {
        $pages = $pageRepository->findByTagName('sport');

        $pagesSortByTags = [];
        foreach ($pages as $page) {
            $tagsName = [];
            foreach ($page->getTags() as $tag) {
                if (!$tag->getName()) {
                    continue;
                }

                if ('sport' === strtolower($tag->getName())) {
                    continue;
                }

                $tagsName[] = $tag->getName();
            }

            if ([] === $tagsName) {
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
