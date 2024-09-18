<?php

declare(strict_types=1);

namespace App\Infrastructure\Mailing;

use App\Domain\Newsletter\NewsletterType;
use Symfony\Component\Uid\Uuid;

final readonly class ActualityNewsletterMessage implements NewsletterMessage
{
    public function __construct(
        private Uuid $actualityUid,
    ) {
    }

    public function getActualityUid(): Uuid
    {
        return $this->actualityUid;
    }

    #[\Override]
    public function getType(): NewsletterType
    {
        return NewsletterType::ACTUALITY_NEW;
    }

    #[\Override]
    public function getContext(): array
    {
        return [
            'actuality' => $this->actualityUid->__toString(),
        ];
    }
}
