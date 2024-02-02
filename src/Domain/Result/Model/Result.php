<?php

declare(strict_types=1);

namespace App\Domain\Result\Model;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Model\Archer;
use App\Domain\Result\Repository\ResultRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use App\Infrastructure\Model\WeaponTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ResultRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: 'NONSTRICT_READ_WRITE')]
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'type', type: Types::STRING)]
#[ORM\DiscriminatorMap(value: [
    'result_competition' => ResultCompetition::class,
    'result_badge' => ResultBadge::class,
    'result_team' => ResultTeam::class,
])]
abstract class Result
{
    use IdTrait;
    use TimestampTrait;
    use WeaponTrait;

    #[ORM\ManyToOne(targetEntity: Archer::class, inversedBy: 'results')]
    private ?Archer $archer = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private ?int $score = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $rank = null;

    // Categories de l'archer
    #[ORM\Column(type: Types::STRING, length: 191, nullable: true, enumType: Category::class)]
    private ?Category $category = null;

    // Est-ce un record personnel de l'archer, ce n'est pas forcément son dernier record
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $record = false;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private ?\DateTimeInterface $completionDate = null;

    #[\Override]
    public function __toString(): string
    {
        return $this->getArcher()?->__toString().' | '.$this->getScore().' points | '.$this->getRank();
    }

    public function getArcher(): ?Archer
    {
        return $this->archer;
    }

    public function setArcher(?Archer $archer): self
    {
        $this->archer = $archer;

        return $this;
    }

    public function getScore(): ?int
    {
        return $this->score;
    }

    public function setScore(?int $score): self
    {
        $this->score = $score;

        return $this;
    }

    public function getRank(): ?int
    {
        return $this->rank;
    }

    public function setRank(?int $rank): self
    {
        $this->rank = $rank;

        return $this;
    }

    public function onThePodium(): bool
    {
        return null !== $this->getRank() && 0 !== $this->getRank() && $this->getRank() <= 3;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getRecord(): ?bool
    {
        return $this->record;
    }

    public function setRecord(bool $record): self
    {
        $this->record = $record;

        return $this;
    }

    public function getCompletionDate(): ?\DateTimeInterface
    {
        return $this->completionDate;
    }

    public function setCompletionDate(?\DateTimeInterface $completionDate): self
    {
        $this->completionDate = $completionDate;

        return $this;
    }
}
