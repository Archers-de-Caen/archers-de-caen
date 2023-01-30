<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Competition\Repository\CompetitionRegisterDepartureRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: CompetitionRegisterDepartureRepository::class)]
final class CompetitionRegisterDeparture
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    #[Groups(['departure'])]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['departure'])]
    private ?int $maxRegistration = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['departure'])]
    private int $numberOfRegistered = 0;

    #[ORM\ManyToOne(targetEntity: CompetitionRegister::class, inversedBy: 'departures')]
    private ?CompetitionRegister $competitionRegister = null;

    /**
     * @var Collection<int, CompetitionRegisterDepartureTarget>
     */
    #[ORM\OneToMany(
        mappedBy: 'departure',
        targetEntity: CompetitionRegisterDepartureTarget::class,
        cascade: ['ALL'],
        orphanRemoval: true
    )]
    #[Groups(['departure'])]
    private Collection $targets;

    public function __construct()
    {
        $this->targets = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getDate()?->format('d/m/Y à H:i') ?? '';
    }

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(?\DateTimeImmutable $date): self
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
        return $this->getNumberOfRegistered();
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

    public function getNumberOfRegistered(): int
    {
        return $this->numberOfRegistered;
    }

    public function setNumberOfRegistered(int $numberOfRegistered): self
    {
        $this->numberOfRegistered = $numberOfRegistered;

        return $this;
    }
}
