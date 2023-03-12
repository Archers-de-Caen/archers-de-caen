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
}
