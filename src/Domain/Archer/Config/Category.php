<?php

declare(strict_types=1);

namespace App\Domain\Archer\Config;

enum Category: string
{
    case PEEWEE = 'peewee'; // Poussin
    case BENJAMIN = 'benjamin'; // Benjamin
    case CUB = 'cub'; // Minime
    case CADET = 'cadet';
    case JUNIOR = 'junior';
    case SENIOR_ONE = 'senior_one';
    case SENIOR_TWO = 'senior_two';
    case SENIOR_THREE = 'senior_three';

    // Anciennes catégories
    case OLD_SENIOR = 'senior';
    case OLD_VETERAN = 'veteran';
    case OLD_GREAT_VETERAN = 'great_veteran';

    public function isAdult(): bool
    {
        return \in_array($this, [
            self::JUNIOR,
            self::SENIOR_ONE,
            self::SENIOR_TWO,
            self::SENIOR_THREE,
        ], true);
    }

    /**
     * @throws \ValueError
     */
    public static function createFromString(string $category): self
    {
        return match ($category) {
            'PH', 'Poussin Homme', 'U11 Homme', 'PF', 'Poussin Femme', 'U11 Femme', 'U11' => self::PEEWEE,
            'BH', 'Benjamin Homme', 'U13 Homme',
            'BF', 'Benjamin Femme', 'Benjamine Femme', 'U13 Femme', 'U13' => self::BENJAMIN,
            'JH', 'Junior Homme', 'U15 Homme', 'JF', 'Junior Femme', 'U15 Femme', 'U15' => self::JUNIOR,
            'MH', 'Minime Homme', 'U18 Homme', 'MF', 'Minime Femme', 'U18 Femme', 'U18' => self::CUB,
            'CH', 'Cadet Homme', 'U21 Homme', 'CF', 'Cadet Femme', 'U21 Femme', 'U21' => self::CADET,
            'S1H', 'Senior 1 Homme', 'S1F', 'Senior 1 Femme', 'S1' => self::SENIOR_ONE,
            'S2H', 'Senior 2 Homme', 'S2F', 'Senior 2 Femme', 'S2' => self::SENIOR_TWO,
            'S3H', 'Senior 3 Homme', 'S3F', 'Senior 3 Femme', 'S3' => self::SENIOR_THREE,

            // Anciennes catégories
            'SH', 'Senior Homme', 'SF', 'Senior Femme', 'S' => self::OLD_SENIOR,
            'VH', 'Vétéran Homme', 'VF', 'Vétéran Femme', 'V' => self::OLD_VETERAN,
            'SVH', 'Super Vétéran Homme', 'SVF', 'Super Vétéran Femme', 'SV' => self::OLD_GREAT_VETERAN,

            default => throw new \ValueError($category.' not found'),
        };
    }
}
