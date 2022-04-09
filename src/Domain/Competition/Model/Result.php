<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Archer\Config\Category;
use App\Domain\Archer\Config\Weapon;
use App\Domain\Archer\Model\Archer;
use App\Domain\Competition\Repository\ResultRepository;
use App\Domain\Shared\Model\IdTrait;
use App\Domain\Shared\Model\TimestampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ResultRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ORM\Cache(usage: "NONSTRICT_READ_WRITE")]
#[ORM\InheritanceType("JOINED")]
#[ORM\DiscriminatorColumn(name: "type", type: "string")]
#[ORM\DiscriminatorMap(value: ["result_competition" => ResultCompetition::class, "result_progress_arrow" => ResultProgressArrow::class])]
abstract class Result
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\ManyToOne(targetEntity: Archer::class, inversedBy: 'results')]
    private ?Archer $archer = null;

    #[ORM\Column(type: Types::INTEGER)]
    private ?int $score = null;

    #[ORM\Column(type: Types::INTEGER, nullable: true)]
    private ?int $rank = null;

    # Categories de l'archer
    #[ORM\Column(type: Types::STRING, enumType: Category::class)]
    private ?Category $category = null;

    # Est-ce un record personnel de l'archer, ce n'est pas forcément son dernier record
    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $record = null;

    #[ORM\Column(type: Types::STRING, enumType: Weapon::class)]
    private ?Weapon $weapon = null;

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

    public function setRecord(?bool $record): self
    {
        $this->record = $record;

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
}
