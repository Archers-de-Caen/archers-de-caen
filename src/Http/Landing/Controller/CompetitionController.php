<?php

declare(strict_types=1);

namespace App\Http\Landing\Controller;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Badge\Model\Badge;
use App\Domain\Badge\Repository\BadgeRepository;
use App\Domain\Competition\Config\Type;
use App\Domain\Competition\Model\Competition;
use App\Domain\Competition\Repository\CompetitionRepository;
use App\Domain\Result\Model\ResultCompetition;
use App\Domain\Result\Repository\ResultCompetitionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class CompetitionController extends AbstractController
{
    public const ROUTE_LANDING_RESULTS = 'landing_results';
    public const ROUTE_LANDING_RESULTS_ARROW = 'landing_results_arrow';
    public const ROUTE_LANDING_RESULTS_COMPETITIONS_SEASONS = 'landing_results_competitions_seasons';
    public const ROUTE_LANDING_RESULTS_COMPETITIONS_SEASON = 'landing_results_competitions_season';
    public const ROUTE_LANDING_RESULTS_COMPETITION = 'landing_results_competition';
    public const ROUTE_LANDING_RESULTS_RECORD = 'landing_results_record';
    public const ROUTE_LANDING_RESULTS_FEDERAL_HONORS = 'landing_results_federal_honors';

    #[Route('/resultats', name: self::ROUTE_LANDING_RESULTS)]
    public function results(): Response
    {
        return $this->render('/landing/results/results.html.twig');
    }

    #[Route('/resultats/fleche-de-progression', name: self::ROUTE_LANDING_RESULTS_ARROW)]
    public function resultsArrow(EntityManagerInterface $em): Response
    {
        $archers = $em->getRepository(Archer::class)->findAll();

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
            'progressArrows' => $em->getRepository(Badge::class)->findProgressArrow(),
        ]);
    }

    #[Route('/resultats/concours', name: self::ROUTE_LANDING_RESULTS_COMPETITIONS_SEASONS)]
    public function resultsCompetitionsSeasons(CompetitionRepository $competitionRepository): Response
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

    #[Route('/resultats/concours/saison/{season}', name: self::ROUTE_LANDING_RESULTS_COMPETITIONS_SEASON)]
    public function resultsCompetitionsSeason(int $season, CompetitionRepository $competitionRepository): Response
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

    #[Route('/resultats/concours/{slug}', name: self::ROUTE_LANDING_RESULTS_COMPETITION)]
    public function resultsCompetitions(Request $request, Competition $competition): Response
    {
        $results = [];
        $participants = [];
        $recordCount = 0;
        $podiumCount = 0;

        foreach (Weapon::toChoices() as $WeaponToString => $weapon) {
            foreach (Category::toChoices() as $categoryToString => $category) {
                foreach ($competition->getResults() as $result) {
                    if (
                        $categoryToString === $result->getCategory()?->toString() &&
                        $WeaponToString === $result->getWeapon()?->toString()
                    ) {
                        if (!isset($results[$WeaponToString])) {
                            $results[$WeaponToString] = [];
                        }

                        if (!isset($results[$WeaponToString][$categoryToString])) {
                            $results[$WeaponToString][$categoryToString] = [];
                        }

                        if (($archer = $result->getArcher()) && !in_array($archer, $participants, true)) {
                            $participants[] = $archer;
                        }

                        if ($result->getRecord()) {
                            ++$recordCount;
                        }

                        if ($result->getRank() <= 3) {
                            ++$podiumCount;
                        }

                        $results[$WeaponToString][$categoryToString][] = $result;
                    }
                }
            }
        }

        $template = '/landing/results/competitions/results-competition.html.twig';
        if ($request->query->get('iframe')) {
            $template = '/landing/results/competitions/results-competition-iframe.html.twig';
        }

        return $this->render($template, [
            'competition' => $competition,
            'results' => $results,
            'participantCount' => count($participants),
            'recordCount' => $recordCount,
            'podiumCount' => $podiumCount,
        ]);
    }

    #[Route('/resultats/record', name: self::ROUTE_LANDING_RESULTS_RECORD)]
    public function resultsRecord(ResultCompetitionRepository $resultCompetitionRepository): Response
    {
        /** @var ResultCompetition[] $resultRecords */
        $resultRecords = $resultCompetitionRepository
            ->createQueryBuilder('rc')

            ->select('rc')
            ->addSelect('c')
            ->addSelect('a')

            ->leftJoin('rc.competition', 'c')
            ->leftJoin('rc.archer', 'a')

            ->getQuery()
            ->getResult();

        $resultRecordsOrdered = [];

        foreach ($resultRecords as $resultRecord) {
            $competition = $resultRecord->getCompetition();

            if (!$competition) {
                continue;
            }

            $type = $competition->getType()?->toString();
            $weapon = $resultRecord->getWeapon()?->toString();
            $archer = $resultRecord->getArcher()?->getId()?->__toString();

            if (!$type || !$weapon || !$archer) {
                continue;
            }

            if (!isset($resultRecordsOrdered[$type])) {
                $resultRecordsOrdered[$type] = [];
            }

            if (!isset($resultRecordsOrdered[$type][$weapon])) {
                $resultRecordsOrdered[$type][$weapon] = [];
            }

            if (
                !isset($resultRecordsOrdered[$type][$weapon][$archer]) ||
                $resultRecordsOrdered[$type][$weapon][$archer]->getScore() < $resultRecord->getScore()
            ) {
                $resultRecordsOrdered[$type][$weapon][$archer] = $resultRecord;
            }
        }

        return $this->render('/landing/results/result-record.html.twig', [
            'resultRecords' => $resultRecordsOrdered,
            'weapons' => Weapon::getInOrder(),
            'competitionTypes' => array_filter(
                Type::getInOrder(),
                static function (Type $competitionType) use ($resultRecordsOrdered) {
                    foreach ($resultRecordsOrdered as $key => $resultRecordOrdered) {
                        if (!empty($resultRecordOrdered) && $key === $competitionType->toString()) {
                            return true;
                        }
                    }

                    return false;
                }
            ),
        ]);
    }

    #[Route('/resultats/distinctions-federales', name: self::ROUTE_LANDING_RESULTS_FEDERAL_HONORS)]
    public function resultsFederalHonors(BadgeRepository $badgeRepository): Response
    {
        $badges = $badgeRepository->findBy(['type' => Badge::COMPETITION]);

        return $this->render('/landing/results/result-badges.html.twig', [
            'badges' => $badges,
            'weapons' => Weapon::getInOrder(),
            'competitionTypes' => array_filter(
                Type::getInOrder(),
                static function (Type $competitionType) use ($badges) {
                    foreach ($badges as $badge) {
                        if (
                            isset($badge->getConditions()['weapon']) &&
                            $badge->getCompetitionType() === $competitionType
                        ) {
                            return true;
                        }
                    }

                    return false;
                }
            ),
        ]);
    }
}
