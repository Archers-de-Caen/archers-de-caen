<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Competition\Repository\ProgressArrowRepository;
use App\Domain\Shared\Model\IdTrait;
use App\Domain\Shared\Model\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProgressArrowRepository::class)]
class ProgressArrow
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING)]
    private ?string $name = null;

    /**
     * @var Collection<int, ResultProgressArrow>
     */
    #[ORM\OneToMany(mappedBy: 'progressArrow', targetEntity: ResultProgressArrow::class, cascade: ['ALL'], orphanRemoval: true)]
    private Collection $results;

    public function __construct()
    {
        $this->results = new ArrayCollection();
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

    /**
     * @return Collection<int, ResultProgressArrow>
     */
    public function getResults(): Collection
    {
        return $this->results;
    }

    public function addResult(ResultProgressArrow $resultProgressArrow): self
    {
        if (!$this->results->contains($resultProgressArrow)) {
            $this->results[] = $resultProgressArrow;
            $resultProgressArrow->setProgressArrow($this);
        }

        return $this;
    }

    public function removeResult(ResultProgressArrow $resultProgressArrow): self
    {
        if ($this->results->removeElement($resultProgressArrow)) {
            // set the owning side to null (unless already changed)
            if ($resultProgressArrow->getProgressArrow() === $this) {
                $resultProgressArrow->setProgressArrow(null);
            }
        }

        return $this;
    }
}
