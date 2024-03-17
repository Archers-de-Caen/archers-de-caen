<?php

declare(strict_types=1);

namespace App\Http\Landing\Request;

final class PaginationDto
{
    public function __construct(
        public ?int $page = 0,
        public ?int $limit = 10,
    ) {
    }
}
