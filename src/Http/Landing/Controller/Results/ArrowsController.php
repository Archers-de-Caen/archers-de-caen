<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Results;

use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Badge\Repository\BadgeRepository;
use App\Http\Landing\Filter\ArrowFilter;
use App\Http\Landing\Request\ArrowFilterDto;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/resultats/fleche-de-progression',
    name: self::ROUTE,
    methods: [
        Request::METHOD_GET,
        Request::METHOD_POST,
    ]
)]
class ArrowsController extends AbstractController
{
    public const ROUTE = 'landing_results_arrow';

    public function __construct(
        private readonly ArcherRepository $archerRepository,
        private readonly BadgeRepository $badgeRepository,
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapQueryString]
        ?ArrowFilterDto $filterDto,
    ): Response {
        $filterForm = $this->createForm(ArrowFilter::class, $filterDto);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filterDto = $filterForm->getData();

            /** @var SubmitButton $resetBtn */
            $resetBtn = $filterForm->get('reset');

            if ($resetBtn->isClicked()) {
                $filterDto = new ArrowFilterDto();
            }

            return $this->redirectToRoute(self::ROUTE, (array) $filterDto);
        }

        $queryBuilder = $this->archerRepository->createQueryBuilder('a');

        if ($filterDto) {
            $this->handleFilter($queryBuilder, $filterDto);
        }

        /** @var Archer[] $archers */
        $archers = $queryBuilder
            ->getQuery()
            ->getResult();

        $archers = array_filter($archers, static fn (Archer $archer) => $archer->getResultsProgressArrow()->count());

        usort(
            $archers,
            static function (Archer $one, Archer $two): int {
                $levelOne = $one->getBestProgressArrowObtained()?->getBadge()?->getLevel();
                $levelTwo = $two->getBestProgressArrowObtained()?->getBadge()?->getLevel();

                return $levelOne > $levelTwo ? -1 : 1;
            }
        );

        return $this->render('/landing/results/result-progress-arrow.html.twig', [
            'archers' => $archers,
            'progressArrows' => $this->badgeRepository->findProgressArrow(),
            'filterForm' => $filterForm->createView(),
        ]);
    }

    private function handleFilter(QueryBuilder $queryBuilder, ArrowFilterDto $filterDto): void
    {
        if ($filterDto->onlyArcherLicenced) {
            $queryBuilder
                ->leftJoin('a.archerLicenses', 'al', 'WITH', 'al.active = TRUE')
                ->andWhere('al.id IS NOT NULL')
            ;
        }
    }
}
