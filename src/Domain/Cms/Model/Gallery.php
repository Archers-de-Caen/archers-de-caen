<?php

declare(strict_types=1);

namespace App\Domain\Cms\Model;

use App\Domain\Cms\Repository\GalleryRepository;
use App\Domain\File\Model\Photo;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation\Slug;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: GalleryRepository::class)]
class Gallery
{
    use IdTrait;
    use StatusTrait;
    use TimestampTrait;

    public const SERIALIZER_GROUP_SHOW = 'gallery_show';

    #[ORM\Column(type: Types::STRING, length: 191)]
    #[Groups([self::SERIALIZER_GROUP_SHOW])]
    private ?string $title;

    #[ORM\Column(type: Types::STRING, length: 191, unique: true)]
    #[Slug(fields: ['title'], unique: true)]
    #[Groups([self::SERIALIZER_GROUP_SHOW])]
    private ?string $slug = null;

    #[ORM\OneToOne(targetEntity: Photo::class, cascade: ['persist'])]
    #[Groups([self::SERIALIZER_GROUP_SHOW])]
    private ?Photo $mainPhoto = null;

    /**
     * @var Collection<int, Photo>
     */
    #[ORM\OneToMany(mappedBy: 'gallery', targetEntity: Photo::class, cascade: ['persist'])]
    #[Groups([self::SERIALIZER_GROUP_SHOW])]
    private Collection $photos;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->getTitle() ?? '';
    }

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

    /**
     * @return Collection<int, Photo>
     */
    public function getPhotos(): Collection
    {
        return $this->photos;
    }

    public function addPhoto(Photo $photo): self
    {
        if (!$this->photos->contains($photo)) {
            $this->photos[] = $photo;
            $photo->setGallery($this);
        }

        return $this;
    }

    public function removePhoto(Photo $photo): self
    {
        // set the owning side to null (unless already changed)
        if ($this->photos->removeElement($photo) && $photo->getGallery() === $this) {
            $photo->setGallery(null);
        }

        return $this;
    }

    public function getMainPhoto(): ?Photo
    {
        return $this->mainPhoto;
    }

    public function setMainPhoto(?Photo $mainPhoto): void
    {
        $this->mainPhoto = $mainPhoto;
    }
}
