<?php

declare(strict_types=1);

namespace App\Infrastructure\Service\FFTA;

readonly class CompetitionResultSearchDTO
{
    public function __construct(
        private int $season,
        private string $discipline = 'all',
        private string $championshipType = 'all',
        private ?\DateTimeInterface $dateStart = null,
        private ?\DateTimeInterface $dateEnd = null,
    ) {
    }

    public function getSeason(): int
    {
        return $this->season;
    }

    public function getDiscipline(): string
    {
        return $this->discipline;
    }

    public function getChampionshipType(): string
    {
        return $this->championshipType;
    }

    public function getDateStart(): ?\DateTimeInterface
    {
        return $this->dateStart;
    }

    public function getDateEnd(): ?\DateTimeInterface
    {
        return $this->dateEnd;
    }
}
