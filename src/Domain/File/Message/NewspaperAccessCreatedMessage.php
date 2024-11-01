<?php

declare(strict_types=1);

namespace App\Domain\File\Message;

class NewspaperAccessCreatedMessage
{
    public function __construct(
        public string $email,
        public string $password,
    ) {
    }
}
