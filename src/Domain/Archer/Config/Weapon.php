<?php

declare(strict_types=1);

namespace App\Domain\Archer\Config;

use App\Infrastructure\Config\Enum;
use ValueError;

enum Weapon: string implements Enum
{
    case RECURVE_BOW = 'recurve_bow';
    case COMPOUND_BOW = 'compound_bow';
    case BARE_BOW = 'bare_bow';

    public function toString(): string
    {
        return match ($this) {
            self::RECURVE_BOW => 'Arc classique',
            self::COMPOUND_BOW => 'Arc à poulies',
            self::BARE_BOW => 'Arc nu',
        };
    }

    public function toArrayValue(): string
    {
        return match ($this) {
            self::RECURVE_BOW => self::RECURVE_BOW->value,
            self::COMPOUND_BOW => self::COMPOUND_BOW->value,
            self::BARE_BOW => self::BARE_BOW->value,
        };
    }

    public static function toChoices(): array
    {
        return array_combine(
            array_map(static fn (Weapon $category) => $category->toString(), self::cases()),
            array_map(static fn (Weapon $category) => $category->toArrayValue(), self::cases())
        );
    }

    public static function toChoicesWithEnumValue(): array
    {
        return array_combine(
            array_map(static fn (Weapon $category) => $category->toString(), self::cases()),
            array_map(static fn (Weapon $category) => $category, self::cases())
        );
    }

    public static function createFromString(string $weapon): self
    {
        return match ($weapon) {
            'CL' => self::RECURVE_BOW,
            'CO' => self::COMPOUND_BOW,
            'BB' => self::BARE_BOW,

            default => throw new ValueError($weapon . ' not found'),
        };
    }

    public static function getInOrder(): array
    {
        return [
            self::RECURVE_BOW,
            self::COMPOUND_BOW,
            self::BARE_BOW,
        ];
    }
}
