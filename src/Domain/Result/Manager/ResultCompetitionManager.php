<?php

declare(strict_types=1);

namespace App\Domain\Result\Manager;

use App\Domain\Badge\Model\Badge;
use App\Domain\Result\Model\Result;
use App\Domain\Result\Model\ResultBadge;
use App\Domain\Result\Model\ResultCompetition;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class ResultCompetitionManager
{
    public function __construct(readonly private EntityManagerInterface $em)
    {
    }

    public function awardingBadges(ResultCompetition $resultCompetition): void
    {
        if (!$archer = $resultCompetition->getArcher()) {
            throw new \UnexpectedValueException('Archer not defined');
        }

        if (!$competition = $resultCompetition->getCompetition()) {
            throw new \UnexpectedValueException('Competition not defined');
        }

        if (!$competition->getType()) {
            throw new \UnexpectedValueException('Type not defined');
        }

        /**
         * Filtrage de tous les badges de l'archer, afin de récupérer seulement ceux qui corresponde au résultat
         * qui s'apprête à être enregistré (persist).
         */
        $resultsBadgeFiltered = $archer->getResultsBadge()
            ->filter(static function (Result $resultBadge) use ($resultCompetition) {
                if (!$resultBadge instanceof ResultBadge) {
                    return false;
                }

                if (!$competition = $resultCompetition->getCompetition()) {
                    return false;
                }

                if (!$resultBadge->getBadge()) {
                    return false;
                }

                if ('competition' !== $resultBadge->getBadge()->getType()) {
                    return false;
                }

                if ($resultBadge->getWeapon() !== $resultCompetition->getWeapon()) {
                    return false;
                }

                if ($resultBadge->getBadge()->getCompetitionType() !== $competition->getType()) {
                    return false;
                }

                return true;
            })
            ->toArray();

        uasort($resultsBadgeFiltered, static function (Result $first, Result $second) {
            return $first->getScore() < $second->getScore() ? -1 : 1;
        });

        /** @var ResultBadge|null $bestResultBadge */
        $bestResultBadge = $resultsBadgeFiltered[0] ?? null;

        /** @var Badge[] $badges */
        $badges = $this->em
            ->getRepository(Badge::class)
            ->createQueryBuilder('b')
            ->where('b.competitionType = :competitionType')
            ->andWhere("JSON_VALUE(b.conditions, '\$.weapon') = :weapon")
            ->andWhere("JSON_VALUE(b.conditions, '\$.type') = 'minScore'")
            ->setParameter('competitionType', $competition->getType()->value)
            ->setParameter('weapon', $resultCompetition->getWeapon()?->value)
            ->getQuery()
            ->getResult();

        uasort($badges, static function (Badge $first, Badge $second) {
            if (!$first->getConditions() || !$second->getConditions()) {
                return 0;
            }

            return $first->getConditions()['score'] < $second->getConditions()['score'] ? -1 : 1;
        });

        foreach ($badges as $badge) {
            if ($badge->getConditions() && $resultCompetition->getScore() > $badge->getConditions()['score']) {
                if (!$bestResultBadge || $badge !== $bestResultBadge->getBadge()) {
                    $resultBadge = new ResultBadge();

                    $resultBadge->setCompletionDate($resultCompetition->getCompletionDate());
                    $resultBadge->setBadge($badge);
                    $resultBadge->setCategory($resultCompetition->getCategory());
                    $resultBadge->setScore($resultCompetition->getScore());
                    $resultBadge->setWeapon($resultCompetition->getWeapon());

                    $archer->addResult($resultBadge);
                }

                break;
            }
        }
    }

    public function awardingRecord(ResultCompetition $resultCompetition): ResultCompetition
    {
        if (!$archer = $resultCompetition->getArcher()) {
            throw new \UnexpectedValueException('Archer not defined');
        }

        if (!$competition = $resultCompetition->getCompetition()) {
            throw new \UnexpectedValueException('Competition not defined');
        }

        if (!$competition->getType()) {
            throw new \UnexpectedValueException('Type not defined');
        }

        // Filtrage des records de l'archer, selon le type de competition et d'arme que l'archer vien d'accomplir.
        $resultsCompetitionFiltered = $archer->getResultsCompetition()
            ->filter(static function (Result $oldResultCompetition) use ($resultCompetition) {
                if (!$oldResultCompetition instanceof ResultCompetition) {
                    return false;
                }

                if (!$competition = $resultCompetition->getCompetition()) {
                    return false;
                }

                if (!$oldResultCompetition->getCompetition()) {
                    return false;
                }

                if ($competition->getType() !== $oldResultCompetition->getCompetition()->getType()) {
                    return false;
                }

                if ($oldResultCompetition->getWeapon() !== $resultCompetition->getWeapon()) {
                    return false;
                }

                if ($oldResultCompetition->getScore() <= $resultCompetition->getScore()) {
                    return false;
                }

                return true;
            });

        if (!$resultsCompetitionFiltered->count()) {
            $resultCompetition->setRecord(true);
        }

        return $resultCompetition;
    }
}
