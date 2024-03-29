<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Competition\Config\TargetType;
use App\Domain\Competition\Repository\CompetitionRegisterDepartureTargetRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetitionRegisterDepartureTargetRepository::class)]
class CompetitionRegisterDepartureTarget
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING, enumType: TargetType::class)]
    private ?TargetType $type = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $distance = null;

    #[ORM\ManyToOne(targetEntity: CompetitionRegisterDeparture::class, inversedBy: 'targets')]
    private ?CompetitionRegisterDeparture $departure = null;

    /**
     * @var Collection<int, CompetitionRegisterDepartureTargetArcher>
     */
    #[ORM\OneToMany(mappedBy: 'target', targetEntity: CompetitionRegisterDepartureTargetArcher::class, cascade: ['ALL'])]
    private Collection $archers;

    #[\Override]
    public function __toString(): string
    {
        return $this->getType()?->toString().' à '.$this->getDistance().' mètres';
    }

    public function __construct()
    {
        $this->archers = new ArrayCollection();
    }

    public function getType(): ?TargetType
    {
        return $this->type;
    }

    public function setType(?TargetType $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getDistance(): ?int
    {
        return $this->distance;
    }

    public function setDistance(?int $distance): self
    {
        $this->distance = $distance;

        return $this;
    }

    public function getDeparture(): ?CompetitionRegisterDeparture
    {
        return $this->departure;
    }

    public function setDeparture(?CompetitionRegisterDeparture $departure): self
    {
        $this->departure = $departure;

        return $this;
    }

    public function getArchers(): Collection
    {
        return $this->archers;
    }

    public function addArcher(CompetitionRegisterDepartureTargetArcher $archer): self
    {
        if (!$this->archers->contains($archer)) {
            $this->archers[] = $archer;

            $archer->setTarget($this);
        }

        return $this;
    }

    public function removeArcher(CompetitionRegisterDepartureTargetArcher $archer): self
    {
        // set the owning side to null (unless already changed)
        if ($this->archers->removeElement($archer) && $archer->getTarget() === $this) {
            $archer->setTarget(null);
        }

        return $this;
    }
}
