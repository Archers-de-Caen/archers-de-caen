<?php

declare(strict_types=1);

namespace App\Domain\Contact\Config;

enum Subject: string
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
}
