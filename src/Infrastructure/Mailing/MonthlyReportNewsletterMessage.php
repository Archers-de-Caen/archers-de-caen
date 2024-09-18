<?php

declare(strict_types=1);

namespace App\Infrastructure\Mailing;

use App\Domain\Newsletter\NewsletterType;
use Symfony\Component\Uid\Uuid;

final readonly class MonthlyReportNewsletterMessage implements NewsletterMessage
{
    /**
     * @param array<Uuid> $actualityUuids
     * @param array<Uuid> $galleryUuids
     * @param array<Uuid> $competitionUuids
     */
    public function __construct(
        private array $actualityUuids,
        private array $galleryUuids,
        private array $competitionUuids,
    ) {
    }

    public function getActualityUuids(): array
    {
        return $this->actualityUuids;
    }

    public function getGalleryUuids(): array
    {
        return $this->galleryUuids;
    }

    public function getCompetitionUuids(): array
    {
        return $this->competitionUuids;
    }

    #[\Override]
    public function getType(): NewsletterType
    {
        return NewsletterType::MONTHLY_REPORT;
    }

    #[\Override]
    public function getContext(): array
    {
        return [
            'actualities' => implode(',', array_map(fn (Uuid $uuid) => $uuid->__toString(), $this->actualityUuids)),
            'galeries' => implode(',', array_map(fn (Uuid $uuid) => $uuid->__toString(), $this->galleryUuids)),
            'competitions' => implode(',', array_map(fn (Uuid $uuid) => $uuid->__toString(), $this->competitionUuids)),
        ];
    }
}
