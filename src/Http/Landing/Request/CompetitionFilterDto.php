<?php

declare(strict_types=1);

namespace App\Http\Landing\Request;

final class CompetitionFilterDto
{
    public function __construct(
        public ?int $season = null,
        public ?string $type = null,
        public ?string $location = null,
    ) {
    }

    public function __serialize(): array
    {
        return [
            'season' => $this->season,
            'type' => $this->type,
            'location' => $this->location,
        ];
    }
}
