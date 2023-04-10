<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller\Results;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Archer\Repository\ArcherRepository;
use App\Domain\Badge\Model\Badge;
use App\Domain\Badge\Repository\BadgeRepository;
use App\Domain\Competition\Config\Type;
use App\Domain\Competition\Model\Competition;
use App\Domain\Competition\Repository\CompetitionRepository;
use App\Domain\Result\Model\ResultCompetition;
use App\Domain\Result\Repository\ResultCompetitionRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

#[AsController]
#[Route(
    path: '/resultats/concours/saison/{season}',
    name: self::ROUTE,
    methods: Request::METHOD_GET
)]
final class CompetitionChoiceController extends AbstractController
{
    public const ROUTE = 'landing_results_competitions_season';

    public function __invoke(int $season, CompetitionRepository $competitionRepository): Response
    {
        $competitions = $competitionRepository
            ->createQueryBuilder('competition')
            ->where('IF(MONTH(competition.dateStart) >= 9, YEAR(competition.dateStart) + 1, YEAR(competition.dateStart)) = :season')
            ->orderBy('competition.dateStart', 'ASC')
            ->setParameter('season', $season)
            ->getQuery()
            ->getResult()
        ;

        return $this->render('/landing/results/competitions/results-competitions.html.twig', [
            'competitions' => $competitions,
            'season' => $season,
        ]);
    }
}
