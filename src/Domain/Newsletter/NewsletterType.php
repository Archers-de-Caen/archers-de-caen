<?php

declare(strict_types=1);

namespace App\Domain\Newsletter;

enum NewsletterType: string
{
    case ACTUALITY_NEW = 'actuality_new';
    case GALLERY_NEW = 'gallery_new';
    case MONTHLY_REPORT = 'monthly_report';

    /**
     * @param string $format html|text
     */
    public function emailTemplatePath(string $format = 'html'): string
    {
        return match ($this) {
            self::ACTUALITY_NEW => sprintf('/mails/newsletter/actuality-new.%s.twig', $format),
            self::GALLERY_NEW => sprintf('/mails/newsletter/gallery-new.%s.twig', $format),
            self::MONTHLY_REPORT => sprintf('/mails/newsletter/monthly-report.%s.twig', $format),
        };
    }

    public function emailSubject(): string
    {
        return match ($this) {
            self::ACTUALITY_NEW => 'Une nouvelle actualité est en ligne !',
            self::GALLERY_NEW => 'Un nouvel album photos est en ligne !',
            self::MONTHLY_REPORT => 'Que s\'est-il passé le mois dernier ?',
        };
    }
}
