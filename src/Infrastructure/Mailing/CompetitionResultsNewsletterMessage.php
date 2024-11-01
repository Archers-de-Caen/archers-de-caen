<?php

declare(strict_types=1);

namespace App\Infrastructure\Mailing;

use App\Domain\Newsletter\NewsletterType;
use Symfony\Component\Uid\Uuid;

final readonly class CompetitionResultsNewsletterMessage implements NewsletterMessage
{
    public function __construct(
        private Uuid $competitionUuid,
        private NewsletterType $type,
    ) {
    }

    public function getCompetitionUuid(): Uuid
    {
        return $this->competitionUuid;
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
            'competition' => $this->competitionUuid->__toString(),
        ];
    }
}
