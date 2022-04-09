<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Competition\Repository\ResultCompetitionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResultCompetitionRepository::class)]
class ResultCompetition extends Result
{
    #[ORM\ManyToOne(targetEntity: Competition::class, inversedBy: 'results')]
    private ?Competition $competition = null;

    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    public function setCompetition(?Competition $competition): self
    {
        $this->competition = $competition;

        return $this;
    }
}
