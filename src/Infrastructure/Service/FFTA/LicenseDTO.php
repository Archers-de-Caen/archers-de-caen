<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\FFTA;

use App\Domain\Archer\Config\Gender;

readonly class LicenseDTO
{
    public function __construct(
        private ?string $license,
        private ?string $firstName,
        private ?string $lastName,
        private ?Gender $gender,
        private ?string $phone,
        private ?string $email,
        private ?string $location,
        private ?string $status,
        private ?\DateTimeInterface $licenseDateStart,
        private ?\DateTimeInterface $licenseDateEnd,
        private ?string $licenseType,
        private ?string $category,
    ) {
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

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function getLicenseDateStart(): ?\DateTimeInterface
    {
        return $this->licenseDateStart;
    }

    public function getLicenseDateEnd(): ?\DateTimeInterface
    {
        return $this->licenseDateEnd;
    }

    public function getLicenseType(): ?string
    {
        return $this->licenseType;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }
}
