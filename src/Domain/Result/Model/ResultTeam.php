<?php

declare(strict_types=1);

namespace App\Domain\Result\Model;

use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Model\Competition;
use App\Domain\Result\Repository\ResultTeamRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResultTeamRepository::class)]
class ResultTeam extends Result
{
    #[ORM\ManyToOne(targetEntity: Competition::class, inversedBy: 'resultsTeams')]
    private ?Competition $competition = null;

    #[ORM\ManyToMany(targetEntity: Archer::class, inversedBy: 'resultsTeams')]
    private Collection $teammates;

    /**
     * @var array<array{
     *     title: string,
     *     score: int,
     *     opponentName: ?string,
     *     opponentScore: ?int
     * }>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $duels = [];

    /**
     * @var array<array{
     *     teamName: string,
     *     rank: int
     * }>
     */
    #[ORM\Column(type: Types::JSON)]
    private array $finalRankings = [];

    public function __construct()
    {
        $this->teammates = new ArrayCollection();
    }

    public function getCompetition(): ?Competition
    {
        return $this->competition;
    }

    public function setCompetition(?Competition $competition): self
    {
        $this->competition = $competition;

        return $this;
    }

    /**
     * @return Collection<int, Archer>
     */
    public function getTeammates(): Collection
    {
        return $this->teammates;
    }

    public function addTeammate(Archer $archer): self
    {
        if (!$this->teammates->contains($archer)) {
            $this->teammates[] = $archer;
            $archer->addResultTeam($this);
        }

        return $this;
    }

    public function removeTeammate(Archer $archer): self
    {
        $this->teammates->removeElement($archer);

        return $this;
    }

    public function getDuels(): ?array
    {
        return $this->duels;
    }

    /**
     * @param array{
     *     title: string,
     *     score: int,
     *     opponentName: ?string,
     *     opponentScore: ?int
     * } $duel
     */
    public function addDuel(array $duel): self
    {
        if (!in_array($duel, $this->duels, true)) {
            $this->duels[] = $duel;
        }

        return $this;
    }

    public function removeDuel(array $duel): self
    {
        $key = array_search($duel, $this->duels, true);

        if (false !== $key) {
            unset($this->duels[$key]);
        }

        return $this;
    }

    public function getFinalRankings(): ?array
    {
        return $this->finalRankings;
    }

    /**
     * @param array{
     *     teamName: string,
     *     rank: int
     * } $finalRanking
     */
    public function addFinalRanking(array $finalRanking): self
    {
        if (!in_array($finalRanking, $this->finalRankings, true)) {
            $this->finalRankings[] = $finalRanking;
        }

        return $this;
    }

    public function removeFinalRanking(?array $finalRanking): self
    {
        $key = array_search($finalRanking, $this->finalRankings, true);

        if (false !== $key) {
            unset($this->finalRankings[$key]);
        }

        return $this;
    }
}
