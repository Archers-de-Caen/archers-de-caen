<?php

declare(strict_types=1);

namespace App\Domain\Competition\Service;

use App\Domain\Archer\Config\Category as ArcherCategory;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Config\Category as PageCategory;
use App\Domain\Cms\Model\Page;
use App\Domain\Competition\Model\Competition;
use App\Domain\Result\Model\ResultCompetition;
use App\Http\Landing\Controller\Results\CompetitionController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

final readonly class CompetitionService
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * Créer une actualité depuis une compétition.
     * L'actualité est créée en brouillon, elle doit être persist et flush.
     */
    public function createActuality(Competition $competition): Page
    {
        $iframeUrl = $this->urlGenerator->generate(CompetitionController::ROUTE, [
            'slug' => $competition->getSlug(),
        ], UrlGeneratorInterface::ABSOLUTE_URL).'?iframe=true';

        return (new Page())
            ->setCategory(PageCategory::ACTUALITY)
            ->setTitle('Résultat du '.$competition->__toString())
            ->setContent('<iframe src="'.$iframeUrl.'" class="fit-height-content"></iframe>')
        ;
    }

    /**
     * @return array{
     *     results: array<string, array<string, array<int, ResultCompetition>>>,
     *     participants: array<int, Archer>,
     *     recordCount: int,
     *     podiumCount: int,
     * }
     */
    public function groupCompetitionResultsByWeaponAndCategories(Competition $competition): array
    {
        $results = [];
        $participants = [];
        $recordCount = 0;
        $podiumCount = 0;

        foreach (Weapon::cases() as $weapon) {
            foreach (ArcherCategory::cases() as $category) {
                foreach ($competition->getResults() as $result) {
                    if (
                        $category->value === $result->getCategory()?->value
                        && $weapon->value === $result->getWeapon()?->value
                    ) {
                        if (!isset($results[$weapon->value])) {
                            $results[$weapon->value] = [];
                        }

                        if (!isset($results[$weapon->value][$category->value])) {
                            $results[$weapon->value][$category->value] = [];
                        }

                        if (($archer = $result->getArcher()) && !\in_array($archer, $participants, true)) {
                            $participants[] = $archer;
                        }

                        if ($result->getRecord()) {
                            ++$recordCount;
                        }

                        if ($result->getRank() <= 3 && $result->getRank() > 0) {
                            ++$podiumCount;
                        }

                        $results[$weapon->value][$category->value][] = $result;
                    }
                }
            }
        }

        // Sort results by score
        foreach ($results as $weapon => $categories) {
            foreach (array_keys($categories) as $category) {
                usort($results[$weapon][$category], static function (ResultCompetition $a, ResultCompetition $b) : int {
                    if (!$a->getRank() && $b->getRank()) {
                        return 1;
                    }

                    if (!$b->getRank() && $a->getRank()) {
                        return -1;
                    }

                    if (!$a->getRank() && !$b->getRank()) {
                        return 0;
                    }

                    return $b->getScore() <=> $a->getScore();
                });
            }
        }

        return [
            'results' => $results,
            'participants' => $participants,
            'recordCount' => $recordCount,
            'podiumCount' => $podiumCount,
        ];
    }

    public function updateAllRanking(Competition $competition): void
    {
        $results = $competition->getResults()->toArray();
        $archers = [];

        usort($results, static fn(ResultCompetition $a, ResultCompetition $b): int => $b->getScore() <=> $a->getScore());

        $previousScore = null;
        foreach ($results as $result) {
            $category = $result->getCategory();
            $weapon = $result->getWeapon();
            if (!$category) {
                continue;
            }

            if (!$weapon) {
                continue;
            }

            if (!isset($archers[$category->value][$weapon->value])) {
                $archers[$category->value][$weapon->value] = [];
            }

            if (\in_array($result->getArcher(), $archers[$category->value][$weapon->value], true)) {
                $result->setRank(null);

                continue;
            }

            if ($result->getScore() !== $previousScore) {
                $previousScore = $result->getScore();

                $archers[$category->value][$weapon->value][] = $result->getArcher();
            }

            $result->setRank(\count($archers[$category->value][$weapon->value]));
        }
    }
}
