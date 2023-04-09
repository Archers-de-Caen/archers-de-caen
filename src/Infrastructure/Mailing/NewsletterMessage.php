<?php

declare(strict_types=1);

namespace App\Infrastructure\Mailing;

use App\Domain\Newsletter\NewsletterType;

interface NewsletterMessage
{
    public function getType(): NewsletterType;
    public function getContext(): array;
}
