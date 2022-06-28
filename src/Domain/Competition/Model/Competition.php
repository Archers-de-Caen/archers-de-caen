<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Competition\Config\Type;
use App\Domain\Competition\Repository\CompetitionRepository;
use App\Domain\Result\Model\ResultCompetition;
use App\Domain\Result\Model\ResultTeam;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompetitionRepository::class)]
class Competition
{
    use IdTrait;
    use TimestampTrait;

    /**
     * @deprecated
     *
     * TODO: a supprimer après la migration
     *
     * L'id utilisé sur l'ancienne version du site
     */
    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $oldId = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $location = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $dateStart = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $dateEnd = null;

    #[ORM\Column(type: Types::STRING, length: 191, enumType: Type::class)]
    #[Assert\NotNull]
    private ?Type $type = null;

    #[ORM\Column(type: Types::STRING, unique: true, nullable: false)]
    #[Slug(fields: ['location'])]
    private ?string $slug = null;

    /**
     * @var Collection<int, ResultCompetition>
     */
    #[ORM\OneToMany(mappedBy: 'competition', targetEntity: ResultCompetition::class, cascade: ['ALL'], orphanRemoval: true)]
    #[Assert\Valid]
    private Collection $results;

    #[ORM\OneToMany(mappedBy: 'competition', targetEntity: ResultTeam::class, cascade: ['ALL'], orphanRemoval: true)]
    #[Assert\Valid]
    private Collection $resultsTeams;

    public function __construct()
    {
        $this->results = new ArrayCollection();
        $this->resultsTeams = new ArrayCollection();
    }

    public function __toString(): string
    {
        return sprintf(
            'concours %s d%s%s du %s au %s',
            $this->getType()?->toString(),
            in_array(strtolower($this->getLocation() ?? '')[0], ['a', 'e', 'i', 'o', 'u', 'y', 'h']) ? "'" : 'e',
            $this->getLocation(),
            $this->getDateStart()?->format('d/m/Y'),
            $this->getDateEnd()?->format('d/m/Y')
        );
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

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(Type $type): self
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

    /**
     * @deprecated
     *
     * TODO: a supprimer après la migration
     */
    public function getOldId(): ?int
    {
        return $this->oldId;
    }

    /**
     * @deprecated
     *
     * TODO: a supprimer après la migration
     */
    public function setOldId(?int $oldId): self
    {
        $this->oldId = $oldId;

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

    public function getResultsTeams(): Collection
    {
        return $this->resultsTeams;
    }

    public function addResultsTeam(ResultTeam $resultTeam): void
    {
        if (!$this->resultsTeams->contains($resultTeam)) {
            $this->resultsTeams[] = $resultTeam;
            $resultTeam->setCompetition($this);
        }
    }

    public function removeResultsTeam(ResultTeam $resultTeam): void
    {
        $this->resultsTeams->removeElement($resultTeam);
        $resultTeam->setCompetition(null);
    }
}
