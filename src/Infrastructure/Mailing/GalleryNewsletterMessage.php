<?php

declare(strict_types=1);

namespace App\Infrastructure\Mailing;

use App\Domain\Newsletter\NewsletterType;
use Symfony\Component\Uid\Uuid;

class GalleryNewsletterMessage implements NewsletterMessage
{
    public function __construct(
        private readonly Uuid $galleryUid,
        private readonly NewsletterType $type,
    ) {
    }

    public function getGalleryUid(): Uuid
    {
        return $this->galleryUid;
    }

    #[\Override]
    public function getType(): NewsletterType
    {
        return $this->type;
    }

    #[\Override]
    public function getContext(): array
    {
        return [
            'gallery' => $this->getGalleryUid()->__toString(),
        ];
    }
}
