<?php

declare(strict_types=1);

namespace App\Http\Landing\Request;

final class RecordFilterDto
{
    public function __construct(
        public ?string $type = null,
        public ?string $weapon = null,
        public ?bool $onlyArcherLicenced = null,
    ) {
    }

    public function __serialize(): array
    {
        return [
            'type' => $this->type,
            'weapon' => $this->weapon,
            'onlyArcherLicenced' => $this->onlyArcherLicenced,
        ];
    }
}
