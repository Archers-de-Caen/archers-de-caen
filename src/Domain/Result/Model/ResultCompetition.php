<?php

declare(strict_types=1);

namespace App\Domain\Result\Model;

use App\Domain\Competition\Model\Competition;
use App\Domain\Result\Repository\ResultCompetitionRepository;
use Doctrine\DBAL\Types\Types;
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
