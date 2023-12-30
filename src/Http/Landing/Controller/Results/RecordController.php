<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Results;

use App\Domain\Result\Model\ResultCompetition;
use App\Domain\Result\Repository\ResultCompetitionRepository;
use App\Http\Landing\Filter\RecordFilter;
use App\Http\Landing\Request\RecordFilterDto;
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
    path: '/resultats/record',
    name: self::ROUTE,
    methods: [
        Request::METHOD_GET,
        Request::METHOD_POST,
    ]
)]
class RecordController extends AbstractController
{
    public const ROUTE = 'landing_results_record';

    public function __construct(
        private readonly ResultCompetitionRepository $resultCompetitionRepository
    ) {
    }

    public function __invoke(
        Request $request,

        #[MapQueryString]
        ?RecordFilterDto $filterDto,
    ): Response {
        $filterForm = $this->createForm(RecordFilter::class, $filterDto);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filterDto = $filterForm->getData();

            /** @var SubmitButton $resetBtn */
            $resetBtn = $filterForm->get('reset');

            if ($resetBtn->isClicked()) {
                $filterDto = new RecordFilterDto();
            }

            return $this->redirectToRoute(self::ROUTE, (array) $filterDto);
        }

        $queryBuilder = $this->resultCompetitionRepository
            ->createQueryBuilder('rc')

            ->select('rc')
            ->addSelect('c')
            ->addSelect('a')

            ->leftJoin('rc.competition', 'c')
            ->leftJoin('rc.archer', 'a')

            ->orderBy('rc.score', 'DESC')
        ;

        if ($filterDto) {
            $this->handleFilter($queryBuilder, $filterDto);
        }

        /** @var ResultCompetition[] $resultRecords */
        $resultRecords = $queryBuilder
            ->getQuery()
            ->getResult();

        return $this->render('/landing/results/result-record.html.twig', [
            'resultRecords' => $resultRecords,
            'filterForm' => $filterForm->createView(),
        ]);
    }

    private function handleFilter(QueryBuilder $queryBuilder, RecordFilterDto $filterDto): void
    {
        if ($filterDto->type) {
            $queryBuilder
                ->andWhere('c.type = :type')
                ->setParameter('type', $filterDto->type)
            ;
        }

        if ($filterDto->weapon) {
            $queryBuilder
                ->andWhere('rc.weapon = :weapon')
                ->setParameter('weapon', $filterDto->weapon)
            ;
        }

        if ($filterDto->onlyArcherLicenced) {
            $queryBuilder
                ->leftJoin('a.archerLicenses', 'al', 'WITH', 'al.active = TRUE')
                ->andWhere('al.id IS NOT NULL')
            ;
        }
    }
}
