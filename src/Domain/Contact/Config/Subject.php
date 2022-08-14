<?php

declare(strict_types=1);

namespace App\Domain\Contact\Config;

use App\Infrastructure\Config\Enum;

enum Subject: string implements Enum
{
    case CLUB = 'club';
    case PRESS = 'press';
    case GDPR = 'gdpr';
    case WEB_SITE = 'web_site';
    case OTHER = 'other';

    public function toString(): string
    {
        return match ($this) {
            self::CLUB => 'Club',
            self::PRESS => 'Presse',
            self::GDPR => 'RGPD',
            self::WEB_SITE => 'Site web',
            self::OTHER => 'Autre',
        };
    }

    public function toArrayValue(): string
    {
        return match ($this) {
            self::CLUB => self::CLUB->value,
            self::PRESS => self::PRESS->value,
            self::GDPR => self::GDPR->value,
            self::WEB_SITE => self::WEB_SITE->value,
            self::OTHER => self::OTHER->value,
        };
    }

    public static function toChoices(): array
    {
        return array_combine(
            array_map(static fn (Subject $category) => $category->toString(), self::cases()),
            array_map(static fn (Subject $category) => $category->toArrayValue(), self::cases())
        );
    }

    public static function toChoicesWithEnumValue(): array
    {
        return array_combine(
            array_map(static fn (Subject $category) => $category->toString(), self::cases()),
            array_map(static fn (Subject $category) => $category, self::cases())
        );
    }
}
