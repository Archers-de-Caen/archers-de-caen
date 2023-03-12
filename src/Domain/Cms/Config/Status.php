<?php

declare(strict_types=1);

namespace App\Domain\Cms\Config;

enum Status: string
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
}
