<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Competition\Config\Type;
use App\Domain\Competition\Repository\CompetitionRegisterRepository;
use App\Domain\File\Model\Document;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompetitionRegisterRepository::class)]
class CompetitionRegister
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $dateStart = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $dateEnd = null;

    #[ORM\Column(type: Types::JSON)]
    #[Assert\NotNull]
    private array $types = [];

    /**
     * @var Collection<int, CompetitionRegisterDeparture>
     */
    #[ORM\OneToMany(mappedBy: 'competitionRegister', targetEntity: CompetitionRegisterDeparture::class, cascade: ['ALL'])]
    private Collection $departures;

    #[ORM\OneToOne(targetEntity: Document::class, cascade: ['ALL'])]
    private ?Document $mandate = null;

    #[ORM\Column(type: Types::STRING, length: 191, unique: true)]
    #[Slug(fields: ['slug'], unique: true)]
    private ?string $slug = null;

    public function __toString(): string
    {
        return sprintf('Concours du %s au %s', $this->getDateStart()?->format('d/m/Y'), $this->getDateEnd()?->format('d/m/y'));
    }

    public function __construct()
    {
        $this->departures = new ArrayCollection();
    }

    public function getDateStart(): ?DateTimeImmutable
    {
        return $this->dateStart;
    }

    public function setDateStart(?DateTimeImmutable $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?DateTimeImmutable
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?DateTimeImmutable $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getTypes(): array
    {
        return $this->types;
    }

    public function setTypes(array $types): self
    {
        $this->types = $types;

        return $this;
    }

    public function addType(Type $type): self
    {
        if (!in_array($type, $this->types, true)) {
            $this->types[] = $type;
        }

        return $this;
    }

    public function removeType(Type $type): self
    {
        $key = array_search($type, $this->types, true);

        if (false !== $key) {
            unset($this->types[$key]);
        }

        return $this;
    }

    public function getMandate(): ?Document
    {
        return $this->mandate;
    }

    public function setMandate(?Document $mandate): void
    {
        $this->mandate = $mandate;
    }

    /**
     * @return Collection<int, CompetitionRegisterDeparture>
     */
    public function getDepartures(): Collection
    {
        return $this->departures;
    }

    public function addDeparture(CompetitionRegisterDeparture $departure): self
    {
        if (!$this->departures->contains($departure)) {
            $this->departures[] = $departure;

            $departure->setCompetitionRegister($this);
        }

        return $this;
    }

    public function removeDeparture(CompetitionRegisterDeparture $departure): self
    {
        if ($this->departures->removeElement($departure)) {
            // set the owning side to null (unless already changed)
            if ($departure->getCompetitionRegister() === $this) {
                $departure->setCompetitionRegister(null);
            }
        }

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Le vrai slug sera accessible après le persist.
     */
    public function autoSetSlug(): void
    {
        $this->slug = $this->__toString();
    }
}
