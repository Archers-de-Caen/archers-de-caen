<?php

declare(strict_types=1);

namespace App\Infrastructure\LiipImagine;

class CacheResolveMessage
{
    public function __construct(
        private readonly string|array $path
    ) {
    }

    public function getPath(): array|string
    {
        return $this->path;
    }
}
