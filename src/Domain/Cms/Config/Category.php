<?php

declare(strict_types=1);

namespace App\Domain\Cms\Config;

use App\Infrastructure\Config\Enum;

enum Category: string implements Enum
{
    case PAGE = 'page';
    case ACTUALITY = 'actuality';
    public function toString(): string
    {
        return match ($this) {
            self::PAGE => 'Page',
            self::ACTUALITY => 'Actualité',
        };
    }

    public function toArrayValue(): string
    {
        return match ($this) {
            self::PAGE => self::PAGE->value,
            self::ACTUALITY => self::ACTUALITY->value,
        };
    }

    public static function toChoices(): array
    {
        return array_combine(
            array_map(fn (Category $category) => $category->toString(), Category::cases()),
            array_map(fn (Category $category) => $category->toArrayValue(), Category::cases())
        );
    }

    public static function toChoicesWithEnumValue(): array
    {
        return array_combine(
            array_map(fn (Category $category) => $category->toString(), Category::cases()),
            array_map(fn (Category $category) => $category, Category::cases())
        );
    }
}
