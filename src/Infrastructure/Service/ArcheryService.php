<?php

declare(strict_types=1);

namespace App\Infrastructure\Service;

final class ArcheryService
{
    public static function getCurrentSeason(): int
    {
        return (int) ((int) date('m') < 9 ? date('Y') : (string) ((int) date('Y') + 1));
    }
}
