<?php

declare(strict_types=1);

namespace App\Http\Landing\Request;

final class BadgeFilterDto
{
    public function __construct(
        public ?string $weapon = null,
        public ?string $badge = null,
        public ?bool $onlyArcherLicenced = null,
    ) {
    }

    public function __serialize(): array
    {
        return [
            'weapon' => $this->weapon,
            'badge' => $this->badge,
            'onlyArcherLicenced' => $this->onlyArcherLicenced,
        ];
    }
}
