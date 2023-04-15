<?php

declare(strict_types=1);

namespace App\Domain\Cms\Config;

enum Category: string
{
    case PAGE = 'page';
    case ACTUALITY = 'actuality';
}
