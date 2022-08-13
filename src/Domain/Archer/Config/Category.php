<?php

declare(strict_types=1);

namespace App\Domain\Archer\Config;

use App\Infrastructure\Config\Enum;
use ValueError;

enum Category: string implements Enum
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
    // Anciennes catégories

    case OLD_SENIOR_MAN = 'senior_man';
    case OLD_SENIOR_WOMAN = 'senior_woman';
    case OLD_VETERAN_MAN = 'veteran_man';
    case OLD_VETERAN_WOMAN = 'veteran_woman';
    case OLD_GREAT_VETERAN_MAN = 'great_veteran_man';
    case OLD_GREAT_VETERAN_WOMAN = 'great_veteran_woman';

    public function toString(): string
    {
        return match ($this) {
            self::PEEWEE_MAN => 'Poussin Homme',
            self::PEEWEE_WOMAN => 'Poussin Femme',

            self::CUB_MAN => 'Minime Homme',
            self::CUB_WOMAN => 'Minime Femme',

            self::BENJAMIN_MAN => 'Benjamin Homme',
            self::BENJAMIN_WOMAN => 'Benjamin Femme',

            self::CADET_MAN => 'Cadet Homme',
            self::CADET_WOMAN => 'Cadet Femme',

            self::JUNIOR_MAN => 'Junior Homme',
            self::JUNIOR_WOMAN => 'Junior Femme',

            self::SENIOR_ONE_MAN => 'Senior 1 Homme',
            self::SENIOR_ONE_WOMAN => 'Senior 1 Femme',

            self::SENIOR_TWO_MAN => 'Senior 2 Homme',
            self::SENIOR_TWO_WOMAN => 'Senior 2 Femme',

            self::SENIOR_THREE_MAN => 'Senior 3 Homme',
            self::SENIOR_THREE_WOMAN => 'Senior 3 Femme',

            // Anciennes catégories

            self::OLD_SENIOR_MAN => 'Senior Homme',
            self::OLD_SENIOR_WOMAN => 'Senior Femme',

            self::OLD_VETERAN_MAN => 'Vétéran Homme',
            self::OLD_VETERAN_WOMAN => 'Vétéran Femme',

            self::OLD_GREAT_VETERAN_MAN => 'Super Vétéran Homme',
            self::OLD_GREAT_VETERAN_WOMAN => 'Super Vétéran Femme',
        };
    }

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

            self::OLD_SENIOR_MAN => 'SH',
            self::OLD_SENIOR_WOMAN => 'SF',

            self::OLD_VETERAN_MAN => 'VH',
            self::OLD_VETERAN_WOMAN => 'VF',

            self::OLD_GREAT_VETERAN_MAN => 'SVH',
            self::OLD_GREAT_VETERAN_WOMAN => 'SVF',
        };
    }

    public function toArrayValue(): string
    {
        return match ($this) {
            self::PEEWEE_MAN => self::PEEWEE_MAN->value,
            self::PEEWEE_WOMAN => self::PEEWEE_WOMAN->value,

            self::BENJAMIN_MAN => self::BENJAMIN_MAN->value,
            self::BENJAMIN_WOMAN => self::BENJAMIN_WOMAN->value,

            self::CUB_MAN => self::CUB_MAN->value,
            self::CUB_WOMAN => self::CUB_WOMAN->value,

            self::CADET_MAN => self::CADET_MAN->value,
            self::CADET_WOMAN => self::CADET_WOMAN->value,

            self::JUNIOR_MAN => self::JUNIOR_MAN->value,
            self::JUNIOR_WOMAN => self::JUNIOR_WOMAN->value,

            self::SENIOR_ONE_MAN => self::SENIOR_ONE_MAN->value,
            self::SENIOR_ONE_WOMAN => self::SENIOR_ONE_WOMAN->value,

            self::SENIOR_TWO_MAN => self::SENIOR_TWO_MAN->value,
            self::SENIOR_TWO_WOMAN => self::SENIOR_TWO_WOMAN->value,

            self::SENIOR_THREE_MAN => self::SENIOR_THREE_MAN->value,
            self::SENIOR_THREE_WOMAN => self::SENIOR_THREE_WOMAN->value,

            // Anciennes catégories

            self::OLD_SENIOR_MAN => self::OLD_SENIOR_MAN->value,
            self::OLD_SENIOR_WOMAN => self::OLD_SENIOR_WOMAN->value,

            self::OLD_VETERAN_MAN => self::OLD_VETERAN_MAN->value,
            self::OLD_VETERAN_WOMAN => self::OLD_VETERAN_WOMAN->value,

            self::OLD_GREAT_VETERAN_MAN => self::OLD_GREAT_VETERAN_MAN->value,
            self::OLD_GREAT_VETERAN_WOMAN => self::OLD_GREAT_VETERAN_WOMAN->value,
        };
    }

    public static function toChoices(): array
    {
        $casesFiltered = array_filter(self::cases(), static fn (Category $category) => !str_starts_with($category->name, 'OLD'));

        return array_combine(
            array_map(static fn (Category $category) => $category->toString(), $casesFiltered),
            array_map(static fn (Category $category) => $category->toArrayValue(), $casesFiltered)
        );
    }

    public static function toChoicesWithEnumValue(): array
    {
        $casesFiltered = array_filter(self::cases(), static fn (Category $category) => !str_starts_with($category->name, 'OLD'));

        return array_combine(
            array_map(static fn (Category $category) => $category->toString(), $casesFiltered),
            array_map(static fn (Category $category) => $category, $casesFiltered)
        );
    }

    /**
     * @throws ValueError
     */
    public static function createFromString(string $category): self
    {
        return match ($category) {
            'PH' => self::PEEWEE_MAN,
            'PF' => self::PEEWEE_WOMAN,

            'BH' => self::BENJAMIN_MAN,
            'BF' => self::BENJAMIN_WOMAN,

            'JH' => self::JUNIOR_MAN,
            'JF' => self::JUNIOR_WOMAN,

            'MH' => self::CUB_MAN,
            'MF' => self::CUB_WOMAN,

            'CH' => self::CADET_MAN,
            'CF' => self::CADET_WOMAN,

            'S1H' => self::SENIOR_ONE_MAN,
            'S1F' => self::SENIOR_ONE_WOMAN,

            'S2H' => self::SENIOR_TWO_MAN,
            'S2F' => self::SENIOR_TWO_WOMAN,

            'S3H' => self::SENIOR_THREE_MAN,
            'S3F' => self::SENIOR_THREE_WOMAN,

             'SH' => self::OLD_SENIOR_MAN,
             'SF' => self::OLD_SENIOR_WOMAN,

             'VH' => self::OLD_VETERAN_MAN,
             'VF' => self::OLD_VETERAN_WOMAN,

             'SVH' => self::OLD_GREAT_VETERAN_MAN,
             'SVF' => self::OLD_GREAT_VETERAN_WOMAN,

            default => throw new ValueError($category.' not found'),
        };
    }

    public function getGender(): string
    {
        $gender = explode('_', $this->value);

        return end($gender);
    }
}
