<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Results;

use App\Domain\Competition\Repository\CompetitionRepository;
use App\Helper\PaginatorHelper;
use App\Http\Landing\Filter\CompetitionFilter;
use App\Http\Landing\Request\CompetitionFilterDto;
use App\Http\Landing\Request\PaginationDto;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/resultats/concours',
    name: self::ROUTE,
    methods: [
        Request::METHOD_GET,
        Request::METHOD_POST,
    ]
)]
class CompetitionListController extends AbstractController
{
    public const ROUTE = 'landing_results_competitions_list';

    public function __construct(
        private readonly CompetitionRepository $competitionRepository,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapQueryString]
        ?CompetitionFilterDto $filterDto,
        #[MapQueryString]
        PaginationDto $pagination = null,
    ): Response {
        $filterForm = $this->createForm(CompetitionFilter::class, $filterDto);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filterDto = $filterForm->getData();

            /** @var SubmitButton $resetBtn */
            $resetBtn = $filterForm->get('reset');

            if ($resetBtn->isClicked()) {
                $filterDto = new CompetitionFilterDto();
            }

            return $this->redirectToRoute(self::ROUTE, (array) $filterDto);
        }

        $queryBuilder = $this->competitionRepository
            ->createQueryBuilder('competition')
            ->orderBy('competition.dateStart', 'ASC')
        ;

        if ($filterDto) {
            $this->handleQueryBuilder($queryBuilder, $filterDto);
        }

        try {
            /** @var array<int> $queryResult */
            $queryResult = $queryBuilder
                ->select('count(competition.id)')
                ->getQuery()
                ->getOneOrNullResult();

            $maxResult = $queryResult[1];
        } catch (NonUniqueResultException $e) {
            $maxResult = 0;

            $this->logger->error($e->getMessage());
        }

        $pageMax = (int) ceil($maxResult / ($pagination?->limit ?: 1));

        $competitions = $queryBuilder
            ->select('competition')

            ->setFirstResult($pagination?->page * $pagination?->limit ?: 0)
            ->setMaxResults($pagination?->limit ?: 10)

            ->getQuery()
            ->getResult()
        ;

        return $this->render('/landing/results/competitions/results-competitions.html.twig', [
            'competitions' => $competitions,
            'filterForm' => $filterForm->createView(),
            'page' => $pagination->page ?? 0,
            'pageMax' => $pageMax,
            'paginator' => PaginatorHelper::pagination(($pagination->page ?? 0) + 1, $pageMax),
        ]);
    }

    private function handleQueryBuilder(QueryBuilder $queryBuilder, CompetitionFilterDto $filterDto): void
    {
        if ($filterDto->season) {
            $queryBuilder
                ->andWhere('IF(MONTH(competition.dateStart) >= 9, YEAR(competition.dateStart) + 1, YEAR(competition.dateStart)) = :season')
                ->setParameter('season', $filterDto->season)
            ;
        }

        if ($filterDto->type) {
            $queryBuilder
                ->andWhere('competition.type = :type')
                ->setParameter('type', $filterDto->type)
            ;
        }

        if ($filterDto->location) {
            $queryBuilder
                ->andWhere('competition.location = :location')
                ->setParameter('location', $filterDto->location)
            ;
        }
    }
}
