<?php

declare(strict_types=1);

namespace App\Http\Landing\Twig;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Model\Data;
use App\Domain\Cms\Model\Gallery;
use App\Domain\Cms\Model\Page;
use App\Domain\Cms\Repository\DataRepository;
use App\Domain\Cms\Repository\PageRepository;
use App\Domain\Competition\Model\Competition;
use Doctrine\ORM\EntityManagerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

final class GetDataExtension extends AbstractExtension implements GlobalsInterface
{
    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
    }

    #[\Override]
    public function getFunctions(): array
    {
        return [
            new TwigFunction('getHeaderData', $this->getHeaderData(...)),
        ];
    }

    public function getGlobals(): array
    {
        $socialNetwork = $this->getSocialNetwork();

        return [
            'social_network' => [
                'facebook' => $socialNetwork['facebook'] ?? null,
                'instagram' => $socialNetwork['instagram'] ?? null,
                'youtube' => $socialNetwork['youtube'] ?? null,
                'tiktok' => $socialNetwork['tiktok'] ?? null,
            ],
        ];
    }

    /**
     * @return array{
     *     actualities: array<Page>,
     *     galleries: array<Gallery>,
     *     sport: array<string, array<Page>>,
     *     competitions: array<Competition>,
     *     clubOtherPages: array<Page>,
     *     messageImportant: ?string
     * }
     */
    public function getHeaderData(): array
    {
        return [
            'actualities' => $this->getActualities(),
            'galleries' => $this->getGalleries(),
            'sport' => $this->getSportPage(),
            'competitions' => $this->getCompetitions(),
            'clubOtherPages' => $this->getClubOtherPages(),
            'messageImportant' => $this->getMessageImportant(),
        ];
    }

    /**
     * @return array<string, array<Page>>
     */
    private function getSportPage(): array
    {
        /** @var PageRepository $pageRepository */
        $pageRepository = $this->em->getRepository(Page::class);

        return $pageRepository->findSportPages();
    }

    /**
     * @return array<Page>
     */
    private function getActualities(): array
    {
        /** @var array<Page> $actualities */
        $actualities = $this->em->getRepository(Page::class)
            ->createQueryBuilder('p')
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
        $galleries = $this->em->getRepository(Gallery::class)
            ->createQueryBuilder('g')
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
        return $this->em->getRepository(Competition::class)
            ->findBy(
                criteria: [],
                orderBy: ['createdAt' => 'DESC'],
                limit: 10
            );
    }

    private function getClubOtherPages(): array
    {
        /** @var PageRepository $pageRepository */
        $pageRepository = $this->em->getRepository(Page::class);

        return $pageRepository->findByTagName('Club autres pages');
    }

    private function getMessageImportant(): ?string
    {
        /** @var DataRepository $dataRepository */
        $dataRepository = $this->em->getRepository(Data::class);

        return $dataRepository->getText(Data::CODE_MESSAGE_IMPORTANT);
    }

    private function getSocialNetwork(): array
    {
        /** @var DataRepository $dataRepository */
        $dataRepository = $this->em->getRepository(Data::class);

        return $dataRepository->findByCode(Data::CODE_SOCIAL_NETWORK)?->getContent()[0] ?? [];
    }
}
