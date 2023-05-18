<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Competition\Repository\TraptaRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TraptaRepository::class)]
class Trapta
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING, unique: true, nullable: false)]
    private ?string $eventName = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $positions = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $rankings = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $matches = null;

    public function getEventName(): ?string
    {
        return $this->eventName;
    }

    public function setEventName(?string $eventName): self
    {
        $this->eventName = $eventName;

        return $this;
    }

    public function getPositions(): ?array
    {
        return $this->positions;
    }

    public function setPositions(?array $positions): self
    {
        $this->positions = $positions;

        return $this;
    }

    public function getRankings(): ?array
    {
        return $this->rankings;
    }

    public function setRankings(?array $rankings): self
    {
        $this->rankings = $rankings;

        return $this;
    }

    public function getMatches(): ?array
    {
        return $this->matches;
    }

    public function setMatches(?array $matches): self
    {
        $this->matches = $matches;

        return $this;
    }
}
