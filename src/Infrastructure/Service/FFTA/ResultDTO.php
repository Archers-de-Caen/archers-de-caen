<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\FFTA;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Gender;
use App\Domain\Archer\Config\Weapon;

class ResultDTO
{
    public function __construct(
        private ?string $code,
        private ?string $license,
        private ?string $firstName,
        private ?string $lastName,
        private ?Gender $gender,
        private ?Category $category,
        private ?Weapon $weapon,
        private ?int $score,
        private ?\DateTimeInterface $dateStart,
        private ?\DateTimeInterface $dateEnd,
        private ?string $location,
    ) {
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function getWeapon(): ?Weapon
    {
        return $this->weapon;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }
}
