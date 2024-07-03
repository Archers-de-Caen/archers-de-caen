<?php

declare(strict_types=1);

namespace App\Domain\Archer\Config;

enum Category: string
{
    case PEEWEE_MAN = 'peewee_man'; // Poussin
    case PEEWEE_WOMAN = 'peewee_woman';
    case BENJAMIN_MAN = 'benjamin_man'; // Benjamin
    case BENJAMIN_WOMAN = 'benjamin_woman';
    case CUB_MAN = 'cub_man'; // Minime
    case CUB_WOMAN = 'cub_woman';
    case CADET_MAN = 'cadet_man';
    case CADET_WOMAN = 'cadet_woman';
    case JUNIOR_MAN = 'junior_man';
    case JUNIOR_WOMAN = 'junior_woman';
    case SENIOR_ONE_MAN = 'senior_one_man';
    case SENIOR_ONE_WOMAN = 'senior_one_woman';
    case SENIOR_TWO_MAN = 'senior_two_man';
    case SENIOR_TWO_WOMAN = 'senior_two_woman';
    case SENIOR_THREE_MAN = 'senior_three_man';
    case SENIOR_THREE_WOMAN = 'senior_three_woman';
    case SCRATCH_MAN = 'scratch_man';
    case SCRATCH_WOMAN = 'scratch_woman';
    // Anciennes catégories

    case OLD_SENIOR_MAN = 'senior_man';
    case OLD_SENIOR_WOMAN = 'senior_woman';
    case OLD_VETERAN_MAN = 'veteran_man';
    case OLD_VETERAN_WOMAN = 'veteran_woman';
    case OLD_GREAT_VETERAN_MAN = 'great_veteran_man';
    case OLD_GREAT_VETERAN_WOMAN = 'great_veteran_woman';

    public function toShortString(): string
    {
        return match ($this) {
            self::PEEWEE_MAN => 'PH',
            self::PEEWEE_WOMAN => 'PF',

            self::BENJAMIN_MAN => 'BH',
            self::BENJAMIN_WOMAN => 'BF',

            self::JUNIOR_MAN => 'JH',
            self::JUNIOR_WOMAN => 'JF',

            self::CUB_MAN => 'MH',
            self::CUB_WOMAN => 'MF',

            self::CADET_MAN => 'CH',
            self::CADET_WOMAN => 'CF',

            self::SENIOR_ONE_MAN => 'S1H',
            self::SENIOR_ONE_WOMAN => 'S1F',

            self::SENIOR_TWO_MAN => 'S2H',
            self::SENIOR_TWO_WOMAN => 'S2F',

            self::SENIOR_THREE_MAN => 'S3H',
            self::SENIOR_THREE_WOMAN => 'S3F',

            self::SCRATCH_MAN => 'SCRATCH-H',
            self::SCRATCH_WOMAN => 'SCRATCH-F',

            self::OLD_SENIOR_MAN => 'SH',
            self::OLD_SENIOR_WOMAN => 'SF',

            self::OLD_VETERAN_MAN => 'VH',
            self::OLD_VETERAN_WOMAN => 'VF',

            self::OLD_GREAT_VETERAN_MAN => 'SVH',
            self::OLD_GREAT_VETERAN_WOMAN => 'SVF',
        };
    }

    public function isOld(): bool
    {
        return \in_array($this, [
            self::OLD_SENIOR_MAN,
            self::OLD_SENIOR_WOMAN,
            self::OLD_VETERAN_MAN,
            self::OLD_VETERAN_WOMAN,
            self::OLD_GREAT_VETERAN_MAN,
            self::OLD_GREAT_VETERAN_WOMAN,
        ], true);
    }

    /**
     * @throws \ValueError
     */
    public static function createFromString(string $category): self
    {
        return match ($category) {
            'PH', 'Poussin Homme', 'U11 Homme' => self::PEEWEE_MAN,
            'PF', 'Poussin Femme', 'U11 Femme' => self::PEEWEE_WOMAN,

            'BH', 'Benjamin Homme', 'U13 Homme' => self::BENJAMIN_MAN,
            'BF', 'Benjamin Femme', 'Benjamine Femme', 'U13 Femme' => self::BENJAMIN_WOMAN,

            'MH', 'Minime Homme', 'U15 Homme' => self::CUB_MAN,
            'MF', 'Minime Femme', 'U15 Femme' => self::CUB_WOMAN,

            'CH', 'Cadet Homme', 'U18 Homme' => self::CADET_MAN,
            'CF', 'Cadet Femme', 'U18 Femme' => self::CADET_WOMAN,

            'JH', 'Junior Homme', 'U21 Homme' => self::JUNIOR_MAN,
            'JF', 'Junior Femme', 'U21 Femme' => self::JUNIOR_WOMAN,

            'S1H', 'Senior 1 Homme', 'S1 Homme' => self::SENIOR_ONE_MAN,
            'S1F', 'Senior 1 Femme', 'S1 Femme' => self::SENIOR_ONE_WOMAN,

            'S2H', 'Senior 2 Homme', 'S2 Homme' => self::SENIOR_TWO_MAN,
            'S2F', 'Senior 2 Femme', 'S2 Femme' => self::SENIOR_TWO_WOMAN,

            'S3H', 'Senior 3 Homme', 'S3 Homme' => self::SENIOR_THREE_MAN,
            'S3F', 'Senior 3 Femme', 'S3 Femme' => self::SENIOR_THREE_WOMAN,

            'SH', 'Senior Homme', 'Non Homme' => self::OLD_SENIOR_MAN, // TODO: Non Homme, voir d'ou ca vien dans l'export de la FFTA
            'SF', 'Senior Femme', 'Non Femme' => self::OLD_SENIOR_WOMAN, // TODO: Non Femme, voir d'ou ca vien dans l'export de la FFTA

            'VH', 'Vétéran Homme' => self::OLD_VETERAN_MAN,
            'VF', 'Vétéran Femme' => self::OLD_VETERAN_WOMAN,

            'SVH', 'Super Vétéran Homme' => self::OLD_GREAT_VETERAN_MAN,
            'SVF', 'Super Vétéran Femme' => self::OLD_GREAT_VETERAN_WOMAN,

            default => throw new \ValueError($category.' not found'),
        };
    }

    public function getGender(): string
    {
        $gender = explode('_', $this->value);

        return end($gender);
    }

    public function isAdult(): bool
    {
        return \in_array($this, [
            self::JUNIOR_MAN,
            self::JUNIOR_WOMAN,

            self::SENIOR_ONE_MAN,
            self::SENIOR_ONE_WOMAN,

            self::SENIOR_TWO_MAN,
            self::SENIOR_TWO_WOMAN,

            self::SENIOR_THREE_MAN,
            self::SENIOR_THREE_WOMAN,
        ], true);
    }
}
