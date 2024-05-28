<?php

declare(strict_types=1);

namespace App\Domain\Competition\Config;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;

enum Type: string
{
    case INDOOR_2x18_M = 'indoor_2_x_18_m';
    case INDOOR_4x18_M = 'indoor_4_x_18_m';
    case INDOOR_2x25_M = 'indoor_2_x_25_m';
    case INDOOR_2x18_M_2x25_M = 'indoor_2_x_18_m_2_x_25_m';
    case FEDERAL_50_M_30_M = 'federal_50_m_30_m';
    case FEDERAL_2x50_M = 'federal_2_x_50_m';
    case CAMPAGNE = 'campagne';
    case FITA = 'fita';
    case FITA_SCRATCH = 'fita_scratch';
    case FITA_4x70_M = '4_x_70_m';
    case BEURSAULT = 'beursault';
    case FLAG_SHOOTING = 'flag_shooting';
    case NATURE = 'nature';
    case THREE_D = 'three_d';
    case FITA_STAR = 'fita_star';
    case OUTDOOR_INTERNATIONAL = 'outdoor_international';
    case OUTDOOR_NATIONAL = 'outdoor_national';
    case GOLDEN_APPLE_CHALLENGE = 'golden_apple_challenge';
    case PROMOTIONAL = 'promotional';
    case SPECIAL_YOUNG = 'special_young';
    case RUN_ARCHERY = 'run_archery';
    case PARA_INDOOR = 'para_indoor';
    case PARA_OUTDOOR = 'para_outdoor';

    public function toString(): string
    {
        return match ($this) {
            self::INDOOR_2x18_M => 'Salle 2x18m',
            self::INDOOR_4x18_M => 'Salle 4x18m',
            self::INDOOR_2x25_M => 'Salle 2x25m',
            self::INDOOR_2x18_M_2x25_M => 'Salle 2x18m+2x25m',
            self::FEDERAL_50_M_30_M => 'Fédéral 30m+50m',
            self::FEDERAL_2x50_M => 'Fédéral 2x50m',
            self::CAMPAGNE => 'Campagne',
            self::FITA => 'FITA',
            self::FITA_4x70_M => 'FITA 4x70m',
            self::FITA_SCRATCH => 'FITA Scratch',
            self::BEURSAULT => 'Beursault',
            self::FLAG_SHOOTING => 'Tir au drapeau',
            self::NATURE => 'Nature',
            self::THREE_D => '3D',
            self::FITA_STAR => 'FITA Star',
            self::OUTDOOR_INTERNATIONAL => 'TAE International',
            self::OUTDOOR_NATIONAL => 'TAE National',
            self::GOLDEN_APPLE_CHALLENGE => "Challenge de la Pomme d'Or",
            self::PROMOTIONAL => 'Promotionnel',
            self::SPECIAL_YOUNG => 'Spécial jeune',
            self::RUN_ARCHERY => 'Run Archery',
            self::PARA_INDOOR => 'Para Indoor',
            self::PARA_OUTDOOR => 'Para Outdoor',
        };
    }

    /**
     * @throws \ValueError
     */
    public static function createFromString(string $type): self
    {
        return match (strtolower($type)) {
            strtolower('Salle 2x18m') => self::INDOOR_2x18_M,
            strtolower('Salle 4x18m') => self::INDOOR_4x18_M,
            strtolower('Salle 2x25m') => self::INDOOR_2x25_M,
            strtolower('Salle 2x25m+2x18m') => self::INDOOR_2x18_M_2x25_M,
            strtolower('Fédéral 30m+50m') => self::FEDERAL_50_M_30_M,
            strtolower('Fédéral 2x50m') => self::FEDERAL_2x50_M,
            strtolower('Campagne') => self::CAMPAGNE,
            strtolower('FITA') => self::FITA,
            strtolower('Beursault') => self::BEURSAULT,
            strtolower('Tir au drapeau') => self::FLAG_SHOOTING,
            strtolower('Nature') => self::NATURE,
            strtolower('3D') => self::THREE_D,
            strtolower('FITA STAR') => self::FITA_STAR,
            strtolower('FITA Scratch') => self::FITA_SCRATCH,
            strtolower('TAE International') => self::OUTDOOR_INTERNATIONAL,
            strtolower('TAE National') => self::OUTDOOR_NATIONAL,
            strtolower("Challenge de la Pomme d'Or") => self::GOLDEN_APPLE_CHALLENGE,
            strtolower('Promotionnel') => self::PROMOTIONAL,

            default => throw new \ValueError($type.' not found'),
        };
    }

    /**
     * @throws \ValueError
     */
    public static function createFromFFTACode(
        string $fftaCode,
        int $distance,
        Category $archerCategory,
        Weapon $weapon,
        int $target
    ): self {
        if ('T' === $fftaCode) {
            if ($weapon->isCompound()) {
                if (80 === $target) {
                    return Type::OUTDOOR_INTERNATIONAL;
                }

                return Type::OUTDOOR_NATIONAL;
            }

            if ($distance > 50) {
                // TODO: Check if the distance is in the range of the category
                return Type::OUTDOOR_INTERNATIONAL;
            }
            return Type::OUTDOOR_NATIONAL;
        }

        return match ($fftaCode) {
            'S' => Type::INDOOR_2x18_M,
            'C' => Type::CAMPAGNE,
            '3' => Type::THREE_D,
            'N' => Type::NATURE,
            'B' => Type::BEURSAULT,
            'P' => Type::SPECIAL_YOUNG,
            'A' => Type::RUN_ARCHERY,
            'H' => Type::PARA_OUTDOOR,
            'I' => Type::PARA_INDOOR,
            default => throw new \ValueError('Competition type not found'),
        };
    }

    public static function getInOrder(): array
    {
        return [
            self::INDOOR_2x18_M,
            self::INDOOR_4x18_M,
            self::INDOOR_2x25_M,
            self::INDOOR_2x18_M_2x25_M,
            self::PARA_INDOOR,

            self::FEDERAL_50_M_30_M,
            self::FEDERAL_2x50_M,

            self::OUTDOOR_INTERNATIONAL,
            self::OUTDOOR_NATIONAL,
            self::PARA_OUTDOOR,
            self::FITA,
            self::FITA_4x70_M,
            self::FITA_SCRATCH,
            self::FITA_STAR,

            self::BEURSAULT,
            self::CAMPAGNE,
            self::FLAG_SHOOTING,
            self::NATURE,
            self::THREE_D,
            self::GOLDEN_APPLE_CHALLENGE,
            self::PROMOTIONAL,
            self::SPECIAL_YOUNG,
            self::RUN_ARCHERY,
        ];
    }

    public function isTAE(): bool
    {
        return \in_array($this, [
            self::OUTDOOR_INTERNATIONAL,
            self::OUTDOOR_NATIONAL,
            self::PARA_OUTDOOR,
            self::FITA,
            self::FEDERAL_2x50_M,
        ]);
    }
}
