<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Results;

use App\Domain\Competition\Model\Competition;
use App\Domain\Competition\Repository\CompetitionRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[Route(
    path: '/resultats/concours',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
class SeasonChoiceController extends AbstractController
{
    public const ROUTE = 'landing_results_competitions_seasons';

    public function __invoke(CompetitionRepository $competitionRepository): Response
    {
        /** @var int[] $seasons */
        $seasons = $competitionRepository
            ->createQueryBuilder('competition')
            ->select('YEAR(competition.dateStart) AS season')
            ->groupBy('season')
            ->orderBy('season', 'DESC')
            ->getQuery()
            ->getSingleColumnResult()
        ;

        try {
            /** @var ?Competition $lastCompetition */
            $lastCompetition = $competitionRepository
                ->createQueryBuilder('competition')
                ->orderBy('competition.dateStart', 'DESC')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult()
            ;

            if ($lastCompetition && 9 <= (int) $lastCompetition->getDateStart()?->format('n')) {
                $seasons[] = ((int) $lastCompetition->getDateStart()?->format('Y')) + 1;
            }
        } catch (NonUniqueResultException) {
            // Nothing, ca ne devrais jamais arriver !
        }

        $seasons = array_unique($seasons);

        rsort($seasons);

        return $this->render('/landing/results/competitions/seasons.html.twig', [
            'seasons' => $seasons,
        ]);
    }
}
