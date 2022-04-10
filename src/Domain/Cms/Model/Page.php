<?php

declare(strict_types=1);

namespace App\Domain\Cms\Model;

use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Config\Status;
use App\Domain\Cms\Repository\PageRepository;
use App\Domain\Shared\Model\IdTrait;
use App\Domain\Shared\Model\SlugTrait;
use App\Domain\Shared\Model\TimestampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PageRepository::class)]
#[ORM\Table(options: ['collate' => 'utf8mb4_unicode_ci', 'charset' => 'utf8mb4'])]
class Page
{
    use IdTrait;
    use TimestampTrait;
    use SlugTrait;

    #[ORM\Column(type: Types::STRING, length: 255)]
    #[Assert\Length(max: 255)]
    #[Assert\NotBlank]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $content = null;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: Category::class)]
    #[Assert\NotNull]
    private ?Category $category = Category::PAGE;

    #[ORM\Column(type: Types::STRING, length: 255, enumType: Status::class)]
    #[Assert\NotNull]
    private ?Status $status = Status::DRAFT;

    #[ORM\ManyToOne(targetEntity: Archer::class, inversedBy: 'pages')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull]
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
