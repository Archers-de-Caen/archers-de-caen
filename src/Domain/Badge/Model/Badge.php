<?php

declare(strict_types=1);

namespace App\Domain\Badge\Model;

use App\Domain\Badge\Repository\BadgeRepository;
use App\Domain\Competition\Config\Type;
use App\Domain\File\Model\Photo;
use App\Domain\Result\Model\ResultBadge;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BadgeRepository::class)]
class Badge
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING)]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, unique: true, nullable: false)]
    private ?string $code = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $official = false;

    #[ORM\Column(type: Types::STRING)]
    private ?string $type = null;

    #[ORM\Column(type: Types::STRING, nullable: true, enumType: Type::class)]
    private ?Type $competitionType = null;

    #[ORM\Column(type: Types::INTEGER)]
    private int $level = 0;

    /**
     * @var Collection<int, ResultBadge>
     */
    #[ORM\OneToMany(mappedBy: 'badge', targetEntity: ResultBadge::class)]
    private Collection $resultBadges;

    /**
     * @var array{
     *    type: string,
     *    score: int,
     *    weapon?: string
     * }|null
     */
    #[ORM\Column(type: Types::JSON)]
    private ?array $conditions = null;

    #[ORM\OneToOne(targetEntity: Photo::class, cascade: ['persist'])]
    private ?Photo $image;

    public function __construct()
    {
        $this->resultBadges = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName() ?? '';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(?string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function isOfficial(): bool
    {
        return $this->official;
    }

    public function setOfficial(bool $official): self
    {
        $this->official = $official;

        return $this;
    }

    /**
     * @return Collection<int, ResultBadge>
     */
    public function getResultBadges(): Collection
    {
        return $this->resultBadges;
    }

    public function addResultBadge(ResultBadge $resultBadge): self
    {
        if (!$this->resultBadges->contains($resultBadge)) {
            $this->resultBadges[] = $resultBadge;

            $resultBadge->setBadge($this);
        }

        return $this;
    }

    public function removeResultBadge(ResultBadge $resultBadge): self
    {
        if ($this->resultBadges->removeElement($resultBadge)) {
            $resultBadge->setBadge(null);
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getCompetitionType(): ?Type
    {
        return $this->competitionType;
    }

    public function setCompetitionType(?Type $competitionType): self
    {
        $this->competitionType = $competitionType;

        return $this;
    }

    public function getLevel(): int
    {
        return $this->level;
    }

    public function setLevel(int $level): self
    {
        $this->level = $level;

        return $this;
    }

    /**
     * @return array{
     *    type: string,
     *    score: int,
     *    weapon?: string
     * }|null
     */
    public function getConditions(): ?array
    {
        return $this->conditions;
    }

    /**
     * @param array{
     *    type: string,
     *    score: int,
     *    weapon?: string
     * }|null $conditions
     *
     * @return $this
     */
    public function setConditions(?array $conditions): self
    {
        $this->conditions = $conditions;

        return $this;
    }

    public function getImage(): ?Photo
    {
        return $this->image;
    }

    public function setImage(?Photo $image): self
    {
        $this->image = $image;

        return $this;
    }
}
