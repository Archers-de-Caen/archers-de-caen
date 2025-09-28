<?php

declare(strict_types=1);

namespace App\Tests\Ressources\Services\Faker;

use Faker\Provider\DateTime;

final class DateTimeImmutableProvider extends DateTime
{
    /**
     * used in alice config file.
     */
    public static function dateTimeImmutableBetween(
        \DateTimeInterface|string $startDate = '-30 years',
        string $endDate = 'now',
        ?string $timezone = null
    ): \DateTimeImmutable {
        $startTimestamp = $startDate instanceof \DateTimeInterface ? (string) $startDate->getTimestamp() : $startDate;
        $dateTime = parent::dateTimeBetween($startTimestamp, $endDate, $timezone);

        return \DateTimeImmutable::createFromMutable($dateTime);
    }
}
