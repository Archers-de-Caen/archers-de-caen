<?php

declare(strict_types=1);

namespace App\Infrastructure\Model;

use App\Domain\Archer\Config\Weapon;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait WeaponTrait
{
    #[ORM\Column(type: Types::STRING, length: 191, enumType: Weapon::class)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private ?Weapon $weapon = null;

    public function getWeapon(): ?Weapon
    {
        return $this->weapon;
    }

    public function setWeapon(?Weapon $weapon): self
    {
        $this->weapon = $weapon;

        return $this;
    }
}
