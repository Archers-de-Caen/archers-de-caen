<?php

declare(strict_types=1);

namespace App\Domain\Cms\Model;

use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Repository\PageRepository;
use App\Domain\File\Model\Photo;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
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
    use StatusTrait;

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

    #[ORM\ManyToOne(targetEntity: Archer::class)]
    private ?Archer $createdBy = null;

    #[ORM\OneToOne(targetEntity: Photo::class, cascade: ['persist'])]
    private ?Photo $image = null;

    /**
     * @var Collection<Tag>
     */
    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'pages')]
    private Collection $tags;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getTitle() ?? 'Erreur: titre non défini';
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

    /**
     * @return Collection<Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): self
    {
        if (!$this->tags->contains($tag)) {
            $this->tags[] = $tag;

            $tag->addPage($this);
        }

        return $this;
    }

    public function removeTag(Tag $tag): self
    {
        $this->tags->removeElement($tag);

        return $this;
    }
}
