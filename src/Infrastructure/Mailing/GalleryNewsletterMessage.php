<?php

declare(strict_types=1);

namespace App\Infrastructure\Mailing;

use App\Domain\Newsletter\NewsletterType;
use Symfony\Component\Uid\Uuid;

final readonly class GalleryNewsletterMessage implements NewsletterMessage
{
    public function __construct(
        private Uuid $galleryUid,
    ) {
    }

    public function getGalleryUid(): Uuid
    {
        return $this->galleryUid;
    }

    #[\Override]
    public function getType(): NewsletterType
    {
        return NewsletterType::GALLERY_NEW;
    }

    #[\Override]
    public function getContext(): array
    {
        return [
            'gallery' => $this->galleryUid->__toString(),
        ];
    }
}
