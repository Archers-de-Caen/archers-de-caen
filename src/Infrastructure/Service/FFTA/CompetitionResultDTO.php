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

    /**
     * @throws \Exception
     */
    public static function createListFromCsv(string $csv): array
    {
        /*
         * 0: "SAISON", 1: "DISCIPLINE", 2: "NO_LICENCE", 3: "NOM_PERSONNE", 4: "PRENOM_PERSONNE", 5: "HORS_F",
         * 6: "SEXE_PERSONNE", 7: "CAT", 8: "CAT_S", 9: "CODE_STRUCTURE", 10: "NOM_STRUCTURE", 11: "ARME", 12: "NIVEAU",
         * 13: "SCORE", 14: "PAILLE", 15: "DIX", 16: "NEUF", 17: "DISTANCE", 18: "BLASON", 19: "D_DEBUT_CONCOURS",
         * 20: "D_FIN_CONCOURS", 21: "LIEU_CONCOURS", 22: "CODE_STRUCTURE_ORGANISATRICE", 23: "NOM_STRUCTURE_ORGANISATRICE",
         * 24: "FORMULE_TIR", 25: "NIVEAU_CHPT", 26: "DETAIL_NIVEAU_CHPT", 27: "DISTINCTION", 28: "PLACE_QUALIF",
         * 29: "SCORE_DIST1", 30: "SCORE_DIST2", 31: "SCORE_DIST3", 32: "SCORE_DIST4", 33: "SCORE_32", 34: "SCORE_16",
         * 35: "SCORE_8", 36: "SCORE_QUART", 37: "SCORE_DEMI", 38: "SCORE_PETITE_FINAL", 39: "SCORE_FINAL", 40: "PLACE_DEF",
         * 41: "NUM_DEPART", 42: "EPRV_NOM", 43: "CAT_TIR", 44: "CAT_CLASS"
         */

        $lines = explode("\n", $csv);
        $results = [];

        foreach (\array_slice($lines, 1) as $line) {
            $data = str_getcsv($line, ';');

            if (\count($data) < 40) {
                continue;
            }

            $results[] = self::createFromCsvRow($data);
        }

        return $results;
    }

    /**
     * @throws \Exception
     */
    public static function createFromCsvRow(array $competitionResult): self
    {
        if (\count($competitionResult) < 40) {
            throw new \Exception('Invalid competition result');
        }

        $gender = Gender::createFromString($competitionResult[6]);
        $genderLongString = match ($gender) {
            Gender::MAN => 'Homme',
            Gender::WOMAN => 'Femme',
            Gender::OTHER, Gender::UNDEFINED => throw new \ValueError('Gender not found '.$competitionResult[6]),
        };
        $weapon = Weapon::createFromString($competitionResult[11]);
        $distance = (int) $competitionResult[17];
        $target = (int) $competitionResult[18];
        $archerCategory = Category::createFromString($competitionResult[7].' '.$genderLongString);
        $competitionTypeFFTACode = $competitionResult[1];
        $competitionType = Type::createFromFFTACode(
            fftaCode: $competitionTypeFFTACode,
            distance: $distance,
            archerCategory: $archerCategory,
            weapon: $weapon,
            target: $target,
        );
        $categoryOverRanking = $competitionResult[8] ? Category::createFromString($competitionResult[8].' '.$genderLongString) : null;

        $dateStart = \DateTimeImmutable::createFromFormat('Y-m-d', $competitionResult[19]);

        if (false === $dateStart) {
            throw new \Exception('Invalid date start format');
        }

        $dateEnd = \DateTimeImmutable::createFromFormat('Y-m-d', $competitionResult[20]);

        if (false === $dateEnd) {
            throw new \Exception('Invalid date end format');
        }

        return new self(
            season: (int) $competitionResult[0],
            discipline: $competitionType,
            licenseNumber: $competitionResult[2],
            lastName: $competitionResult[3],
            firstName: $competitionResult[4],
            outOfFrance: $competitionResult[5],
            gender: $gender,
            category: $archerCategory,
            categoryOverRanking: $categoryOverRanking,
            structureCode: $competitionResult[9],
            structureName: $competitionResult[10],
            weapon: $weapon,
            level: $competitionResult[12],
            score: (int) $competitionResult[13],
            straw: (int) $competitionResult[14],
            ten: (int) $competitionResult[15],
            nine: (int) $competitionResult[16],
            distance: $distance,
            target: $target,
            startCompetitionDate: $dateStart,
            endCompetitionDate: $dateEnd,
            location: $competitionResult[21],
            organizerStructureCode: $competitionResult[22],
            organizerStructureName: $competitionResult[23],
            shootingFormula: $competitionResult[24],
            championshipLevel: $competitionResult[25],
            championshipLevelDetail: $competitionResult[26],
            distinction: $competitionResult[27],
            qualificationPlace: (int) $competitionResult[28],
            scoreDist1: (int) $competitionResult[29],
            scoreDist2: (int) $competitionResult[30],
            scoreDist3: (int) $competitionResult[31],
            scoreDist4: (int) $competitionResult[32],
            score32: $competitionResult[33] ? (int) $competitionResult[33] : null,
            score16: $competitionResult[34] ? (int) $competitionResult[34] : null,
            score8: $competitionResult[35] ? (int) $competitionResult[35] : null,
            scoreQuarter: $competitionResult[36] ? (int) $competitionResult[36] : null,
            scoreSemi: $competitionResult[37] ? (int) $competitionResult[37] : null,
            scoreSmallFinal: $competitionResult[38] ? (int) $competitionResult[38] : null,
            scoreFinal: $competitionResult[39] ? (int) $competitionResult[39] : null,
            finalPlace: (int) $competitionResult[40],
            startNumber: (int) $competitionResult[41],
            eprvName: $competitionResult[42],
            shootingCategory: $competitionResult[43] ?? null,
            shootingCategoryClass: $competitionResult[44] ?? null,
        );
    }

    public function getEventCode(): string
    {
        return sprintf(
            '%s_%s_%s_%s',
            $this->structureCode,
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
