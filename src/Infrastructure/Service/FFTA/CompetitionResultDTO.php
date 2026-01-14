<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\FFTA;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Competition\Config\Type;

final readonly class CompetitionResultDTO
{
    public function __construct(
        private int $season,
        private Type $discipline,
        private string $licenseNumber,
        private string $lastName,
        private string $firstName,
        private ?string $outOfFrance,
        private Gender $gender,
        private Category $category,
        private ?Category $categoryOverRanking,
        private string $structureCode,
        private string $structureName,
        private Weapon $weapon,
        private ?string $level,
        private int $score,
        private int $straw,
        private int $ten,
        private int $nine,
        private int $distance,
        private int $target,
        private \DateTimeImmutable $startCompetitionDate,
        private \DateTimeImmutable $endCompetitionDate,
        private string $location,
        private string $organizerStructureCode,
        private string $organizerStructureName,
        private ?string $shootingFormula,
        private ?string $championshipLevel,
        private ?string $championshipLevelDetail,
        private ?string $distinction,
        private int $qualificationPlace,
        private int $scoreDist1,
        private int $scoreDist2,
        private int $scoreDist3,
        private int $scoreDist4,
        private ?int $score32,
        private ?int $score16,
        private ?int $score8,
        private ?int $scoreQuarter,
        private ?int $scoreSemi,
        private ?int $scoreSmallFinal,
        private ?int $scoreFinal,
        private int $finalPlace,
        private int $startNumber,
        private string $eprvName,
        private ?string $shootingCategory,
        private ?string $shootingCategoryClass,
    ) {
    }

    public function getEventCode(): string
    {
        return \sprintf(
            '%s_%s_%s_%s',
            $this->organizerStructureCode,
            $this->startCompetitionDate->format('Ymd'),
            $this->endCompetitionDate->format('Ymd'),
            $this->discipline->value,
        );
    }

    public function getCompletionDate(): \DateTimeImmutable
    {
        if ($this->startCompetitionDate === $this->endCompetitionDate) {
            return $this->startCompetitionDate;
        }

        if (1 === $this->startNumber) {
            return $this->startCompetitionDate;
        }

        if ($this->startNumber >= 3) {
            return $this->endCompetitionDate;
        }

        if (2 === $this->startNumber && $this->discipline->isTAE()) {
            return $this->endCompetitionDate;
        }

        return $this->startCompetitionDate;
    }

    public function getSeason(): int
    {
        return $this->season;
    }

    public function getDiscipline(): Type
    {
        return $this->discipline;
    }

    public function getLicenseNumber(): string
    {
        return $this->licenseNumber;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getOutOfFrance(): ?string
    {
        return $this->outOfFrance;
    }

    public function getGender(): Gender
    {
        return $this->gender;
    }

    public function getCategory(): Category
    {
        return $this->category;
    }

    public function getCategoryOverRanking(): ?Category
    {
        return $this->categoryOverRanking;
    }

    public function getStructureCode(): string
    {
        return $this->structureCode;
    }

    public function getStructureName(): string
    {
        return $this->structureName;
    }

    public function getWeapon(): Weapon
    {
        return $this->weapon;
    }

    public function getLevel(): ?string
    {
        return $this->level;
    }

    public function getScore(): int
    {
        return $this->score;
    }

    public function getStraw(): int
    {
        return $this->straw;
    }

    public function getTen(): int
    {
        return $this->ten;
    }

    public function getNine(): int
    {
        return $this->nine;
    }

    public function getDistance(): int
    {
        return $this->distance;
    }

    public function getTarget(): int
    {
        return $this->target;
    }

    public function getStartCompetitionDate(): \DateTimeImmutable
    {
        return $this->startCompetitionDate;
    }

    public function getEndCompetitionDate(): \DateTimeImmutable
    {
        return $this->endCompetitionDate;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getOrganizerStructureCode(): string
    {
        return $this->organizerStructureCode;
    }

    public function getOrganizerStructureName(): string
    {
        return $this->organizerStructureName;
    }

    public function getShootingFormula(): ?string
    {
        return $this->shootingFormula;
    }

    public function getChampionshipLevel(): ?string
    {
        return $this->championshipLevel;
    }

    public function getChampionshipLevelDetail(): ?string
    {
        return $this->championshipLevelDetail;
    }

    public function getDistinction(): ?string
    {
        return $this->distinction;
    }

    public function getQualificationPlace(): int
    {
        return $this->qualificationPlace;
    }

    public function getScoreDist1(): int
    {
        return $this->scoreDist1;
    }

    public function getScoreDist2(): int
    {
        return $this->scoreDist2;
    }

    public function getScoreDist3(): int
    {
        return $this->scoreDist3;
    }

    public function getScoreDist4(): int
    {
        return $this->scoreDist4;
    }

    public function getScore32(): ?int
    {
        return $this->score32;
    }

    public function getScore16(): ?int
    {
        return $this->score16;
    }

    public function getScore8(): ?int
    {
        return $this->score8;
    }

    public function getScoreQuarter(): ?int
    {
        return $this->scoreQuarter;
    }

    public function getScoreSemi(): ?int
    {
        return $this->scoreSemi;
    }

    public function getScoreSmallFinal(): ?int
    {
        return $this->scoreSmallFinal;
    }

    public function getScoreFinal(): ?int
    {
        return $this->scoreFinal;
    }

    public function getFinalPlace(): int
    {
        return $this->finalPlace;
    }

    public function getStartNumber(): int
    {
        return $this->startNumber;
    }

    public function getEprvName(): string
    {
        return $this->eprvName;
    }

    public function getShootingCategory(): ?string
    {
        return $this->shootingCategory;
    }

    public function getShootingCategoryClass(): ?string
    {
        return $this->shootingCategoryClass;
    }
}
