<?php

declare(strict_types=1);

namespace App\Domain\Archer\Config;

enum Gender: string
{
    case MAN = 'man';
    case WOMAN = 'woman';
    case OTHER = 'other';
    case UNDEFINED = 'undefined';

    public function toShortString(): string
    {
        return match ($this) {
            self::MAN => 'M.',
            self::WOMAN => 'Mme.',
            self::OTHER, self::UNDEFINED => '',
        };
    }

    public static function createFromString(string $gender): self
    {
        return match ($gender) {
            'M.', 'M', 'Monsieur', 'Homme' => self::MAN,
            'Mme.', 'Mme', 'Madame', 'Femme' => self::WOMAN,

            default => throw new \ValueError($gender.' not found'),
        };
    }

    public function toOpenGraphValue(): string
    {
        return match ($this) {
            self::MAN => 'male',
            self::WOMAN => 'female',
            self::OTHER, self::UNDEFINED => '',
        };
    }
}
