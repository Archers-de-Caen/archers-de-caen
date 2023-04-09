<?php

declare(strict_types=1);

namespace App\Infrastructure\Mailing;

use App\Domain\Newsletter\NewsletterType;
use Symfony\Component\Uid\Uuid;

class ActualityNewsletterMessage implements NewsletterMessage
{
    public function __construct(
        private readonly Uuid $actualityUid,
        private readonly NewsletterType $type,
    ) {
    }

    public function getActualityUid(): Uuid
    {
        return $this->actualityUid;
    }

    public function getType(): NewsletterType
    {
        return $this->type;
    }

    public function getContext(): array
    {
        return [
            'actuality' => $this->getActualityUid()->__toString(),
        ];
    }
}
