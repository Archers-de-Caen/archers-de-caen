<?php

declare(strict_types=1);

namespace App\Domain\Cms\Model;

use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Repository\DataRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DataRepository::class)]
#[ORM\Table(options: ['collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'])]
final class Data
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING, length: 191, unique: true)]
    #[Assert\Length(max: 191)]
    #[Assert\NotBlank]
    private ?string $code = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $content = null;

    #[ORM\Column(type: Types::STRING, length: 191)]
    #[Assert\Length(max: 191)]
    #[Assert\NotBlank]
    private ?string $formType = null;

    #[ORM\ManyToOne(targetEntity: Archer::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Archer $createdBy = null;

    public function __toString(): string
    {
        return $this->getCode() ?? 'Erreur: code nom défini';
    }

    // Getter / Setter

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): self
    {
        $this->code = $code;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(array $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedBy(): ?Archer
    {
        return $this->createdBy;
    }

    public function setCreatedBy(?Archer $createdBy): self
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    public function getFormType(): ?string
    {
        return $this->formType;
    }

    public function setFormType(?string $formType): void
    {
        $this->formType = $formType;
    }
}
