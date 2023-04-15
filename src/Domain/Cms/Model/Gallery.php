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

#[ORM\Entity(repositoryClass: GalleryRepository::class)]
final class Gallery
{
    use IdTrait;
    use TimestampTrait;
    use StatusTrait;

    #[ORM\Column(type: Types::STRING, length: 191)]
    private ?string $title;

    #[ORM\Column(type: Types::STRING, length: 191, unique: true)]
    #[Slug(fields: ['title'], unique: true)]
    private ?string $slug = null;

    #[ORM\OneToOne(inversedBy: 'galleryMainPhoto', targetEntity: Photo::class, cascade: ['persist'])]
    private ?Photo $mainPhoto = null;

    /**
     * @var Collection<int, Photo>
     */
    #[ORM\OneToMany(mappedBy: 'gallery', targetEntity: Photo::class)]
    private Collection $photos;

    public function __construct()
    {
        $this->photos = new ArrayCollection();
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
        if ($this->photos->removeElement($photo)) {
            // set the owning side to null (unless already changed)
            if ($photo->getGallery() === $this) {
                $photo->setGallery(null);
            }
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
