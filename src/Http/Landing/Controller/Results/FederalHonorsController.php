<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Results;

use App\Domain\Badge\Model\Badge;
use App\Domain\Result\Repository\ResultBadgeRepository;
use App\Http\Landing\Filter\BadgeFilter;
use App\Http\Landing\Request\BadgeFilterDto;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapQueryString;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[Route(
    path: '/resultats/distinctions-federales',
    name: self::ROUTE,
    methods: [
        Request::METHOD_GET,
        Request::METHOD_POST,
    ]
)]
final class FederalHonorsController extends AbstractController
{
    public const string ROUTE = 'landing_results_federal_honors';

    public function __construct(
        private readonly ResultBadgeRepository $resultBadgeRepository,
    ) {
    }

    public function __invoke(
        Request $request,
        #[MapQueryString]
        ?BadgeFilterDto $filterDto
    ): Response {
        $filterForm = $this->createForm(BadgeFilter::class, $filterDto);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $filterDto = $filterForm->getData();

            /** @var SubmitButton $resetBtn */
            $resetBtn = $filterForm->get('reset');

            if ($resetBtn->isClicked()) {
                $filterDto = new BadgeFilterDto();
            }

            return $this->redirectToRoute(self::ROUTE, (array) $filterDto);
        }

        $queryBuilder = $this->resultBadgeRepository
            ->createQueryBuilder('rb')
            ->leftJoin('rb.badge', 'b')

            ->andWhere('b.type = :type')
            ->setParameter('type', Badge::COMPETITION)
        ;

        if ($filterDto instanceof BadgeFilterDto) {
            $this->handleFilter($queryBuilder, $filterDto);
        }

        $resultBadges = $queryBuilder
            ->getQuery()
            ->getResult()
        ;

        return $this->render('/landing/results/result-badges.html.twig', [
            'resultBadges' => $resultBadges,
            'filterForm' => $filterForm->createView(),
        ]);
    }

    private function handleFilter(QueryBuilder $queryBuilder, BadgeFilterDto $filterDto): void
    {
        if ($filterDto->weapon) {
            $queryBuilder
                ->andWhere('rb.weapon = :weapon')
                ->setParameter('weapon', $filterDto->weapon)
            ;
        }

        if ($filterDto->badge) {
            $queryBuilder
                ->andWhere('rb.badge = :badge')
                ->setParameter('badge', $filterDto->badge, 'uuid')
            ;
        }

        if ($filterDto->onlyArcherLicenced) {
            $queryBuilder
                ->leftJoin('rb.archer', 'a')
                ->leftJoin('a.archerLicenses', 'al', 'WITH', 'al.active = TRUE')
                ->andWhere('al.id IS NOT NULL')
            ;
        }
    }
}
