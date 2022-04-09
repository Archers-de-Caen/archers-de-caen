<?php

declare(strict_types=1);

namespace App\Domain\Cms\Config;

use App\Domain\Shared\Config\Enum;

enum Status: string implements Enum
{
    case DRAFT = 'draft';
    case PUBLISH = 'publish';
    case DELETE = 'delete';
    public function toString(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PUBLISH => 'Publier',
            self::DELETE => 'Supprimer',
        };
    }

    public function toArrayValue(): string
    {
        return match ($this) {
            self::DRAFT => self::DRAFT->value,
            self::PUBLISH => self::PUBLISH->value,
            self::DELETE => self::DELETE->value,
        };
    }

    public static function toChoices(): array
    {
        return array_combine(
            array_map(fn (Status $status) => $status->toString(), Status::cases()),
            array_map(fn (Status $status) => $status->toArrayValue(), Status::cases())
        );
    }

    public static function toChoicesWithEnumValue(): array
    {
        return array_combine(
            array_map(fn (Status $status) => $status->toString(), Status::cases()),
            array_map(fn (Status $status) => $status, Status::cases())
        );
    }
}
