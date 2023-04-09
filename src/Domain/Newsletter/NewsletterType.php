<?php

declare(strict_types=1);

namespace App\Domain\Newsletter;

enum NewsletterType: string
{
    case ACTUALITY_NEW = 'actuality_new';
    case GALLERY_NEW = 'gallery_new';

    /**
     * @param string $format html|text
     */
    public function emailTemplatePath(string $format = 'html'): string
    {
        return match ($this) {
            self::ACTUALITY_NEW => "/mails/newsletter/actuality-new.$format.twig",
            self::GALLERY_NEW => "/mails/newsletter/gallery-new.$format.twig",
        };
    }

    public function emailSubject(): string
    {
        return match ($this) {
            self::ACTUALITY_NEW => 'Une nouvelle actualité est en ligne !',
            self::GALLERY_NEW => 'Un nouvel album photos est en ligne !',
        };
    }
}
