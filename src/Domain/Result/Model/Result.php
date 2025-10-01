<?php

declare(strict_types=1);

namespace App\Domain\Result\Model;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\File\Model\Document;
use App\Domain\Result\Repository\ResultRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
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
abstract class Result implements \Stringable
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\ManyToOne(targetEntity: Archer::class, inversedBy: 'results')]
    private ?Archer $archer = null;

    #[ORM\Column(type: Types::STRING, length: 191, enumType: Weapon::class)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private ?Weapon $weapon = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Assert\NotNull]
    #[Assert\NotBlank]
    private ?int $score = null;

    #[ORM\Column(name: '`rank`', type: Types::INTEGER, nullable: true)]
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

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $metadata = null;

    #[ORM\OneToOne(targetEntity: Document::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
    private ?Document $scoreSheet = null;

    #[\Override]
    public function __toString(): string
    {
        return $this->getArcher()?->__toString().' | '.$this->getScore().' points | '.$this->getRank();
    }

    public function getType(): string
    {
        return (new \ReflectionClass($this))->getShortName();
    }

    public function getTitle(): string
    {
        if ($this instanceof ResultCompetition) {
            return $this->getCompetition()?->__toString() ?? '';
        }

        if ($this instanceof ResultBadge) {
            return $this->getBadge()?->__toString() ?? '';
        }

        if ($this instanceof ResultTeam) {
            return $this->getCompetition()?->__toString() ?? '';
        }

        return '';
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

    public function getWeapon(): ?Weapon
    {
        return $this->weapon;
    }

    public function setWeapon(?Weapon $weapon): self
    {
        $this->weapon = $weapon;

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

    public function getMetadata(): ?array
    {
        return $this->metadata;
    }

    public function setMetadata(?array $metadata): self
    {
        $this->metadata = $metadata;

        return $this;
    }

    public function getScoreSheet(): ?Document
    {
        return $this->scoreSheet;
    }

    public function setScoreSheet(?Document $scoreSheet): self
    {
        $this->scoreSheet = $scoreSheet;

        return $this;
    }
}
