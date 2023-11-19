<?php

declare(strict_types=1);

namespace App\Infrastructure\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait LicenseNumberTrait
{
    #[ORM\Column(
        type: Types::STRING,
        length: 8,
        unique: self::LICENSE_NUMBER_UNIQUE,
        nullable: true
    )]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\Length(max: 8)]
    #[Assert\Regex('/[0-9]{7}[A-Za-z]/')]
    private ?string $licenseNumber = null;

    public function getLicenseNumber(): ?string
    {
        return $this->licenseNumber;
    }

    public function setLicenseNumber(?string $licenseNumber): self
    {
        if ($licenseNumber) {
            $this->licenseNumber = strtoupper($licenseNumber);
        }

        return $this;
    }
}
