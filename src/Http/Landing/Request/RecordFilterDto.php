<?php

declare(strict_types=1);

namespace App\Http\Landing\Request;

use App\Domain\Archer\Config\Weapon;
use App\Domain\Competition\Config\Type;

final class RecordFilterDto
{
    public function __construct(
        public ?string $type = Type::INDOOR_2x18_M->value,
        public ?string $weapon = Weapon::RECURVE_BOW->value,
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
