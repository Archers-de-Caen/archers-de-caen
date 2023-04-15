<?php

declare(strict_types=1);

namespace App\Domain\File\Model;

use App\Domain\Cms\Model\Gallery;
use App\Domain\File\Repository\PhotoRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use App\Infrastructure\Model\TokenTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotNull;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable() // For easyadmin bug with new annotation
 */
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: PhotoRepository::class)]
class Photo implements UploadableInterface
{
    use IdTrait;
    use TimestampTrait;
    use TokenTrait;
    public const PREFIX_TOKEN = 'pho';

    /**
     * @Vich\UploadableField(
     *     mapping="photo",
     *     fileNameProperty="imageName",
     *     size="imageSize",
     *     mimeType="imageMimeType",
     *     originalName="imageOriginalName",
     *     dimensions="imageDimension",
     * ) // For easyadmin bug with new annotation
     */
    #[Vich\UploadableField(
        mapping: 'photo',
        fileNameProperty: 'imageName',
        size: 'imageSize',
        mimeType: 'imageMimeType',
        originalName: 'imageOriginalName',
        dimensions: 'imageDimension',
    )]
    #[NotBlank]
    #[NotNull]
    #[Image(maxSize: '128M', maxSizeMessage: 'Image trop lourde')]
    private ?File $imageFile = null;

    #[ORM\Column(type: Types::STRING, length: 191)]
    #[Groups(['Photo'])]
    private ?string $imageName = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['Photo'])]
    private ?int $imageSize = null;

    #[ORM\Column(type: Types::STRING, length: 191)]
    #[Groups(['Photo'])]
    private ?string $imageMimeType = null;

    #[ORM\Column(type: Types::STRING, length: 191)]
    #[Groups(['Photo'])]
    private ?string $imageOriginalName = null;

    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    #[Groups(['Photo'])]
    private ?array $imageDimension = [];

    #[ORM\ManyToOne(targetEntity: Gallery::class, inversedBy: 'photos')]
    private ?Gallery $gallery = null;

    #[ORM\OneToOne(mappedBy: 'mainPhoto', targetEntity: Gallery::class)]
    private ?Gallery $galleryMainPhoto = null;

    public function __toString(): string
    {
        return $this->imageName ?? '';
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setImageFile(File|UploadedFile|null $imageFile = null): self
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): self
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
    }

    public function setImageSize(?int $imageSize): self
    {
        $this->imageSize = $imageSize;

        return $this;
    }

    public function getImageMimeType(): ?string
    {
        return $this->imageMimeType;
    }

    public function setImageMimeType(?string $imageMimeType): self
    {
        $this->imageMimeType = $imageMimeType;

        return $this;
    }

    public function getImageOriginalName(): ?string
    {
        return $this->imageOriginalName;
    }

    public function setImageOriginalName(?string $imageOriginalName): self
    {
        $this->imageOriginalName = $imageOriginalName;

        return $this;
    }

    public function getImageDimension(): ?array
    {
        return $this->imageDimension;
    }

    public function setImageDimension(?array $imageDimension): self
    {
        $this->imageDimension = $imageDimension;

        return $this;
    }

    public function getGallery(): ?Gallery
    {
        return $this->gallery;
    }

    public function setGallery(?Gallery $gallery): self
    {
        $this->gallery = $gallery;

        return $this;
    }

    public function getGalleryMainPhoto(): ?Gallery
    {
        return $this->galleryMainPhoto;
    }

    public function setGalleryMainPhoto(?Gallery $galleryMainPhoto): self
    {
        $this->galleryMainPhoto = $galleryMainPhoto;

        return $this;
    }
}
