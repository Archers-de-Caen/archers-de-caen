<?php

declare(strict_types=1);

namespace App\Domain\Archer\Config;

use App\Infrastructure\Config\Enum;
use ValueError;

enum Gender: string implements Enum
{
    case MAN = 'man';
    case WOMAN = 'woman';
    case OTHER = 'other';
    case UNDEFINED = 'undefined';

    public function toString(): string
    {
        return match ($this) {
            self::MAN => 'Homme',
            self::WOMAN => 'Femme',
            self::OTHER => 'Autre',
            self::UNDEFINED => 'Non défini',
        };
    }

    public function toShortString(): string
    {
        return match ($this) {
            self::MAN => 'M.',
            self::WOMAN => 'Mme.',
            self::OTHER, self::UNDEFINED => '',
        };
    }

    public function toArrayValue(): string
    {
        return match ($this) {
            self::MAN => self::MAN->value,
            self::WOMAN => self::WOMAN->value,
            self::OTHER => self::OTHER->value,
            self::UNDEFINED => self::UNDEFINED->value,
        };
    }

    public static function toChoices(): array
    {
        return array_combine(
            array_map(static fn (Gender $category) => $category->toString(), self::cases()),
            array_map(static fn (Gender $category) => $category->toArrayValue(), self::cases())
        );
    }

    public static function toChoicesBasic(): array
    {
        return [
            self::MAN->toString() => self::MAN,
            self::WOMAN->toString() => self::WOMAN,
        ];
    }

    public static function toChoicesWithEnumValue(): array
    {
        return array_combine(
            array_map(static fn (Gender $category) => $category->toString(), self::cases()),
            array_map(static fn (Gender $category) => $category, self::cases())
        );
    }

    public static function createFromString(string $gender): self
    {
        return match ($gender) {
            'M.', 'M', 'Monsieur', 'Homme' => self::MAN,
            'Mme.', 'Mme', 'Madame', 'Femme' => self::WOMAN,

            default => throw new ValueError($gender.' not found'),
        };
    }
}
