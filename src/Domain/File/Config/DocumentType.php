<?php

declare(strict_types=1);

namespace App\Domain\File\Config;

use App\Infrastructure\Config\Enum;

enum DocumentType: string implements Enum
{
    case NEWSPAPER = 'newspaper';
    case OTHER = 'other';
    public function toString(): string
    {
        return match ($this) {
            self::NEWSPAPER => 'Gazette',
            self::OTHER => 'Autre',
        };
    }

    public function toArrayValue(): string
    {
        return match ($this) {
            self::NEWSPAPER => self::NEWSPAPER->value,
            self::OTHER => self::OTHER->value,
        };
    }

    public static function toChoices(): array
    {
        return array_combine(
            array_map(static fn (DocumentType $documentType) => $documentType->toString(), self::cases()),
            array_map(static fn (DocumentType $documentType) => $documentType->toArrayValue(), self::cases())
        );
    }

    public static function toChoicesWithEnumValue(): array
    {
        return array_combine(
            array_map(static fn (DocumentType $category) => $category->toString(), self::cases()),
            array_map(static fn (DocumentType $category) => $category, self::cases())
        );
    }
}
