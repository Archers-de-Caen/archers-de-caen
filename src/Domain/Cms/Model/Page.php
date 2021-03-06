<?php

declare(strict_types=1);

namespace App\Domain\Cms\Model;

use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Repository\PageRepository;
use App\Domain\File\Model\Photo;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table(options: ['collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'])]
class Page
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING, length: 191)]
    #[Assert\Length(max: 191)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(type: Types::STRING, length: 191, unique: true)]
    #[Slug(fields: ['title'], unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $content = null;

    #[ORM\Column(type: Types::STRING, length: 191, enumType: Category::class)]
    #[Assert\NotNull]
    private ?Category $category = Category::PAGE;

    #[ORM\Column(type: Types::STRING, length: 191, enumType: Status::class)]
    #[Assert\NotNull]
    private ?Status $status = Status::DRAFT;

    #[ORM\ManyToOne(targetEntity: Archer::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?Archer $createdBy = null;

    #[ORM\OneToOne(targetEntity: Photo::class, cascade: ['persist'])]
    private ?Photo $image = null;

    public function __toString(): string
    {
        return $this->getTitle() ?? 'Erreur: titre nom défini';
    }

    // Getter / Setter

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): self
    {
        $this->status = $status;

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
