<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Competition\Repository\CompetitionRepository;
use App\Domain\Shared\Model\IdTrait;
use App\Domain\Shared\Model\TimestampTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetitionRepository::class)]
class Competition
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING)]
    private ?string $location = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $dateStart = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $dateEnd = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $type = null;

    /**
     * @var Collection<int, ResultCompetition>
     */
    #[ORM\OneToMany(mappedBy: 'competition', targetEntity: ResultCompetition::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $results;

    public function __construct()
    {
        $this->results = new ArrayCollection();
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
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

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return Collection<int, ResultCompetition>
     */
    public function getResults(): Collection
    {
        return $this->results;
    }

    public function addResult(ResultCompetition $resultCompetition): self
    {
        if (!$this->results->contains($resultCompetition)) {
            $this->results[] = $resultCompetition;
            $resultCompetition->setCompetition($this);
        }

        return $this;
    }

    public function removeResult(ResultCompetition $resultCompetition): self
    {
        if ($this->results->removeElement($resultCompetition)) {
            // set the owning side to null (unless already changed)
            if ($resultCompetition->getCompetition() === $this) {
                $resultCompetition->setCompetition(null);
            }
        }

        return $this;
    }
}
