<?php

declare(strict_types=1);

namespace App\Domain\Contact\Config;

enum Subject: string
{
    case CLUB = 'club';
    case BEGINNER = 'beginner';
    case LICENSE = 'license';
    case COMPETITION = 'competition';
    case OTHER = 'other';
}
