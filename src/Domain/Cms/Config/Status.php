<?php

declare(strict_types=1);

namespace App\Domain\Cms\Config;

use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

enum Status: string implements TranslatableInterface
{
    case DRAFT = 'draft';
    case PUBLISH = 'publish';
    case DELETE = 'delete';

    public function trans(TranslatorInterface $translator, ?string $locale = null): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::PUBLISH => 'Publier',
            self::DELETE => 'Supprimer',
        };
    }
}
