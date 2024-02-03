<?php

declare(strict_types=1);

namespace App\Http\Landing\Request;

final class ArrowFilterDto
{
    public function __construct(
        public ?bool $onlyArcherLicenced = null,
    ) {
    }

    public function __serialize(): array
    {
        return [
            'onlyArcherLicenced' => $this->onlyArcherLicenced,
        ];
    }
}
