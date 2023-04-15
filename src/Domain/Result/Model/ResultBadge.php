<?php

declare(strict_types=1);

namespace App\Domain\Result\Model;

use App\Domain\Badge\Model\Badge;
use App\Domain\Result\Repository\ResultBadgeRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResultBadgeRepository::class)]
class ResultBadge extends Result
{
    #[ORM\ManyToOne(targetEntity: Badge::class, inversedBy: 'resultBadges')]
    private ?Badge $badge = null;

    public function __toString(): string
    {
        return $this->getBadge()?->__toString().' '.$this->getArcher()?->__toString();
    }

    public function getBadge(): ?Badge
    {
        return $this->badge;
    }

    public function setBadge(?Badge $badge): self
    {
        $this->badge = $badge;

        return $this;
    }
}
