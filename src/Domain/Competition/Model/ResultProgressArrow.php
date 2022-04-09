<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Competition\Repository\ResultProgressArrowRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResultProgressArrowRepository::class)]
class ResultProgressArrow extends Result
{
    #[ORM\ManyToOne(targetEntity: ProgressArrow::class, inversedBy: 'results')]
    private ?ProgressArrow $progressArrow = null;

    public function getProgressArrow(): ?ProgressArrow
    {
        return $this->progressArrow;
    }

    public function setProgressArrow(?ProgressArrow $progressArrow): self
    {
        $this->progressArrow = $progressArrow;

        return $this;
    }
}
