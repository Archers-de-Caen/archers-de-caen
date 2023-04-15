<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Competition\Repository\CompetitionRegisterDepartureTargetArcherRepository;
use App\Infrastructure\Model\ArcherCategoryTrait;
use App\Infrastructure\Model\EmailTrait;
use App\Infrastructure\Model\FirstNameTrait;
use App\Infrastructure\Model\GenderTrait;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\LastNameTrait;
use App\Infrastructure\Model\LicenseNumberTrait;
use App\Infrastructure\Model\PhoneTrait;
use App\Infrastructure\Model\TimestampTrait;
use App\Infrastructure\Model\WeaponTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompetitionRegisterDepartureTargetArcherRepository::class)]
#[ORM\UniqueConstraint(name: 'unique_archer_by_target', columns: ['target_id', 'license_number'])]
final class CompetitionRegisterDepartureTargetArcher
{
    use ArcherCategoryTrait;
    use EmailTrait;
    use FirstNameTrait;
    use GenderTrait;
    use IdTrait;
    use LastNameTrait;
    use LicenseNumberTrait;
    use PhoneTrait;
    use TimestampTrait;
    use WeaponTrait;

    private const LICENSE_NUMBER_UNIQUE = false;
    private const EMAIL_UNIQUE = false;

    public function __toString(): string
    {
        return $this->getLicenseNumber() ?? '';
    }

    #[ORM\Column(type: Types::STRING, length: 191)]
    #[Assert\Length(max: 191)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    private ?string $club = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $wheelchair = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $firstYear = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $additionalInformation = null;

    #[ORM\ManyToOne(targetEntity: CompetitionRegisterDepartureTarget::class, inversedBy: 'archers')]
    private ?CompetitionRegisterDepartureTarget $target = null;

    #[ORM\Column(type: Types::STRING, length: 127, nullable: true)]
    private ?string $position = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: false, options: ['default' => false])]
    private ?bool $paid = false;

    public function getClub(): ?string
    {
        return $this->club;
    }

    public function setClub(?string $club): self
    {
        $this->club = $club;

        return $this;
    }

    public function getTarget(): ?CompetitionRegisterDepartureTarget
    {
        return $this->target;
    }

    public function setTarget(?CompetitionRegisterDepartureTarget $target): void
    {
        $this->target = $target;
    }

    public function getWheelchair(): ?bool
    {
        return $this->wheelchair;
    }

    public function setWheelchair(?bool $wheelchair): self
    {
        $this->wheelchair = $wheelchair;

        return $this;
    }

    public function getFirstYear(): ?bool
    {
        return $this->firstYear;
    }

    public function setFirstYear(?bool $firstYear): self
    {
        $this->firstYear = $firstYear;

        return $this;
    }

    public function getAdditionalInformation(): ?string
    {
        return $this->additionalInformation;
    }

    public function setAdditionalInformation(?string $additionalInformation): self
    {
        $this->additionalInformation = $additionalInformation;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(?string $position): void
    {
        $this->position = $position;
    }

    public function isPaid(): bool
    {
        return $this->paid ?? false;
    }

    public function setPaid(?bool $paid): self
    {
        $this->paid = $paid;

        return $this;
    }
}
