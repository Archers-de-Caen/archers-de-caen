<?php

declare(strict_types=1);

namespace App\Http\Landing\Twig;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Model\Gallery;
use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Repository\GalleryRepository;
use App\Domain\Cms\Repository\PageRepository;
use App\Domain\Competition\Model\Competition;
use App\Domain\Competition\Repository\CompetitionRepository;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class GetHeaderDataExtension extends AbstractExtension
{
    public function __construct(
        private readonly PageRepository $pageRepository,
        private readonly GalleryRepository $galleryRepository,
        private readonly CompetitionRepository $competitionRepository,
    ) {
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('getHeaderData', [$this, 'getHeaderData']),
        ];
    }

    /**
     * @return array{actualities: array<Page>, galleries: array<Gallery>, sport: array<string, array<Page>>, competitions: array<Competition>}
     */
    public function getHeaderData(): array
    {
        return [
            'actualities' => $this->getActualities(),
            'galleries' => $this->getGalleries(),
            'sport' => $this->getSportPage(),
            'competitions' => $this->getCompetitions(),
        ];
    }

    /**
     * @return array<string, array<Page>>
     */
    private function getSportPage(): array
    {
        $pages = $this->pageRepository->findByTagName('sport');

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

        return $pagesSortByTags;
    }

    /**
     * @return array<Page>
     */
    private function getActualities(): array
    {
        /** @var array<Page> $actualities */
        $actualities = $this->pageRepository->createQueryBuilder('p')
            ->select('p', 't')
            ->leftJoin('p.tags', 't')

            ->where('p.category = :category')
            ->setParameter('category', Category::ACTUALITY->value)

            ->andWhere('p.status = :status')
            ->setParameter('status', Status::PUBLISH->value)

            ->orderBy('p.createdAt', 'DESC')

            ->setMaxResults(10)

            ->getQuery()
            ->getResult()
        ;

        return $actualities;
    }

    /**
     * @return array<Gallery>
     */
    private function getGalleries(): array
    {
        /** @var array<Gallery> $galleries */
        $galleries = $this->galleryRepository->createQueryBuilder('g')
            ->select('g', 'mainPhoto')
            ->leftJoin('g.mainPhoto', 'mainPhoto')

            ->where('g.status = :status')
            ->setParameter('status', Status::PUBLISH)

            ->orderBy('g.createdAt', 'DESC')

            ->setMaxResults(8)

            ->getQuery()
            ->getResult()
        ;

        return $galleries;
    }

    /**
     * @return array<Competition>
     */
    private function getCompetitions(): array
    {
        return $this->competitionRepository->findBy(
            criteria: [],
            orderBy: ['createdAt' => 'DESC'],
            limit: 10
        );
    }
}
