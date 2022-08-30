<?php

declare(strict_types=1);

namespace App\Domain\Archer\Config;

use App\Infrastructure\Config\Enum;
use ValueError;

enum LicenseType: string implements Enum
{
    case ADULT = 'adult';
    case YOUNG = 'young';
    case PARASPORTS = 'parasports';
    case OTHER = 'other';

    public function toString(): string
    {
        return match ($this) {
            self::ADULT => 'Adulte',
            self::YOUNG => 'Jeunes',
            self::PARASPORTS => 'Handisport',
            self::OTHER => 'Autre',
        };
    }

    public function toArrayValue(): string
    {
        return match ($this) {
            self::ADULT => self::ADULT->value,
            self::YOUNG => self::YOUNG->value,
            self::PARASPORTS => self::PARASPORTS->value,
            self::OTHER => self::OTHER->value,
        };
    }

    public static function toChoices(): array
    {
        return array_combine(
            array_map(static fn (self $licenseType) => $licenseType->toString(), self::cases()),
            array_map(static fn (self $licenseType) => $licenseType->toArrayValue(), self::cases())
        );
    }

    public static function toChoicesWithEnumValue(): array
    {
        return array_combine(
            array_map(static fn (self $licenseType) => $licenseType->toString(), self::cases()),
            array_map(static fn (self $licenseType) => $licenseType, self::cases())
        );
    }
}
