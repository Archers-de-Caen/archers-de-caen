<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Competition\Repository\CompetitionRegisterDepartureRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetitionRegisterDepartureRepository::class)]
class CompetitionRegisterDeparture
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $maxRegistration = null;

    #[ORM\ManyToOne(targetEntity: CompetitionRegister::class, inversedBy: 'departures')]
    private ?CompetitionRegister $competitionRegister = null;

    /**
     * @var Collection<int, CompetitionRegisterDepartureTarget>
     */
    #[ORM\OneToMany(mappedBy: 'departure', targetEntity: CompetitionRegisterDepartureTarget::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $targets;

    public function __construct()
    {
        $this->targets = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getDate()?->format('d/m/Y à H:i') ?? '';
    }

    public function getDate(): ?DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getMaxRegistration(): ?int
    {
        return $this->maxRegistration;
    }

    public function setMaxRegistration(?int $maxRegistration): self
    {
        $this->maxRegistration = $maxRegistration;

        return $this;
    }

    public function getRegistration(): int
    {
        $count = 0;

        foreach ($this->getTargets() as $target) {
            $count += $target->getArchers()->count();
        }

        return $count;
    }

    public function getCompetitionRegister(): ?CompetitionRegister
    {
        return $this->competitionRegister;
    }

    public function setCompetitionRegister(?CompetitionRegister $competitionRegister): void
    {
        $this->competitionRegister = $competitionRegister;
    }

    /**
     * @return Collection<int, CompetitionRegisterDepartureTarget>
     */
    public function getTargets(): Collection
    {
        return $this->targets;
    }

    public function addTarget(CompetitionRegisterDepartureTarget $target): self
    {
        if (!$this->targets->contains($target)) {
            $this->targets[] = $target;

            $target->setDeparture($this);
        }

        return $this;
    }

    public function removeTarget(CompetitionRegisterDepartureTarget $target): self
    {
        if ($this->targets->contains($target)) {
            $this->targets->removeElement($target);

            // set the owning side to null (unless already changed)
            if ($target->getDeparture() === $this) {
                $target->setDeparture(null);
            }
        }

        return $this;
    }
}
