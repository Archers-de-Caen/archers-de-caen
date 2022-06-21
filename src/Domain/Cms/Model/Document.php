<?php

declare(strict_types=1);

namespace App\Domain\Cms\Model;

use App\Domain\Cms\Repository\DocumentRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use App\Infrastructure\Model\TokenTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * @Vich\Uploadable() // For easyadmin bug with new annotation
 */
#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document implements UploadableInterface
{
    use IdTrait;
    use TimestampTrait;
    use TokenTrait;
    public const PREFIX_TOKEN = 'doc';

    /**
     * @Vich\UploadableField(
     *     mapping="document",
     *     fileNameProperty="documentName",
     *     size="documentSize",
     *     mimeType="documentMimeType",
     *     originalName="documentOriginalName",
     * ) // For easyadmin bug with new annotation
     */
    #[Vich\UploadableField(
        mapping: 'document',
        fileNameProperty: 'documentName',
        size: 'documentSize',
        mimeType: 'imageMimeType',
        originalName: 'documentOriginalName',
    )]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    #[Assert\File(maxSize: 2000000, maxSizeMessage: 'Document trop lourde')]
    private ?File $documentFile = null;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['Document'])]
    private ?string $documentName = null;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['Document'])]
    private ?int $documentSize = null;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['Document'])]
    private ?string $documentMimeType = null;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['Document'])]
    private ?string $documentOriginalName = null;

    public function __toString(): string
    {
        return $this->documentName ?? '';
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     */
    public function setDocumentFile(File|UploadedFile|null $imageFile = null): self
    {
        $this->documentFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getDocumentFile(): ?File
    {
        return $this->documentFile;
    }

    public function getDocumentName(): ?string
    {
        return $this->documentName;
    }

    public function setDocumentName(?string $documentName): void
    {
        $this->documentName = $documentName;
    }

    public function getDocumentSize(): ?int
    {
        return $this->documentSize;
    }

    public function setDocumentSize(?int $documentSize): void
    {
        $this->documentSize = $documentSize;
    }

    public function getDocumentMimeType(): ?string
    {
        return $this->documentMimeType;
    }

    public function setDocumentMimeType(?string $documentMimeType): void
    {
        $this->documentMimeType = $documentMimeType;
    }

    public function getDocumentOriginalName(): ?string
    {
        return $this->documentOriginalName;
    }

    public function setDocumentOriginalName(?string $documentOriginalName): void
    {
        $this->documentOriginalName = $documentOriginalName;
    }
}
