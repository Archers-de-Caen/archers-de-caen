<?php

declare(strict_types=1);

namespace App\Domain\License\Model;

use App\Domain\License\Config\LicenseType;
use App\Domain\License\Repository\LicenseRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LicenseRepository::class)]
class License
{
    use IdTrait;
    use TimestampTrait;

    /**
     * @var Collection<int, ArcherLicense>
     */
    #[ORM\OneToMany(mappedBy: 'license', targetEntity: ArcherLicense::class, orphanRemoval: true)]
    private Collection $archerLicenses;

    #[ORM\Column(type: Types::STRING, length: 191)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\Length(max: 191)]
    private ?string $title = null;

    /**
     * @var int|null Le prix en centime
     */
    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    #[Assert\PositiveOrZero]
    private ?int $price = null;

    #[ORM\Column(type: Types::STRING, enumType: LicenseType::class)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private ?LicenseType $type = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private ?string $description = null;

    public function __construct()
    {
        $this->archerLicenses = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getTitle().' - '.($this->getPrice()/100).'€';
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getPrice(): ?int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getType(): ?LicenseType
    {
        return $this->type;
    }

    public function setType(LicenseType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, ArcherLicense>
     */
    public function getArcherLicenses(): Collection
    {
        return $this->archerLicenses;
    }

    public function addArcherLicense(ArcherLicense $archerLicense): self
    {
        if (!$this->archerLicenses->contains($archerLicense)) {
            $this->archerLicenses[] = $archerLicense;
            $archerLicense->setLicense($this);
        }

        return $this;
    }

    public function removeArcherLicense(ArcherLicense $archerLicense): self
    {
        if ($this->archerLicenses->removeElement($archerLicense)) {
            // set the owning side to null (unless already changed)
            if ($this === $archerLicense->getLicense()) {
                $archerLicense->setLicense(null);
            }
        }

        return $this;
    }
}
