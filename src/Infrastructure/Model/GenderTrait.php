<?php

declare(strict_types=1);

namespace App\Infrastructure\Model;

use App\Domain\Archer\Config\Gender;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait GenderTrait
{
    #[ORM\Column(type: Types::STRING, length: 191, nullable: true, enumType: Gender::class)]
    private ?Gender $gender = null;

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(?Gender $gender): self
    {
        $this->gender = $gender;

        return $this;
    }
}
