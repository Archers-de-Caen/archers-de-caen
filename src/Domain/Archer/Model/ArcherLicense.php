<?php

declare(strict_types=1);

namespace App\Domain\Archer\Model;

use App\Domain\Archer\Repository\ArcherLicenseRepository;
use App\Infrastructure\Model\ArcherCategoryTrait;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArcherLicenseRepository::class)]
class ArcherLicense
{
    use ArcherCategoryTrait;
    use IdTrait;
    use TimestampTrait;

    #[ORM\ManyToOne(targetEntity: Archer::class, inversedBy: 'archerLicenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Archer $archer = null;

    #[ORM\ManyToOne(targetEntity: License::class, inversedBy: 'archerLicenses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?License $license = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateStart;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $dateEnd;

    #[ORM\Column(type: Types::BOOLEAN, options: ['default' => false])]
    private ?bool $active = false;

    #[\Override]
    public function __toString(): string
    {
        return $this->getLicense()?->getTitle() ?? '';
    }

    public function getArcher(): ?Archer
    {
        return $this->archer;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function setDateStart(?\DateTimeInterface $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?\DateTimeInterface $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function setArcher(?Archer $archer): self
    {
        $this->archer = $archer;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        if ($active) {
            foreach ($this->getArcher() ? $this->getArcher()->getArcherLicenses() : [] as $archerLicense) {
                $archerLicense->setActive(false);
            }
        }

        $this->active = $active;

        return $this;
    }

    public function getLicense(): ?License
    {
        return $this->license;
    }

    public function setLicense(?License $license): self
    {
        $this->license = $license;

        return $this;
    }
}
