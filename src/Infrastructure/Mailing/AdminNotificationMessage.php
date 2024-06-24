<?php

declare(strict_types=1);

namespace App\Infrastructure\Mailing;

final readonly class AdminNotificationMessage
{
    public function __construct(
        private string $subject,
        private string $templatePath,
        private array $templateData,
    ) {
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getTemplatePath(): string
    {
        return $this->templatePath;
    }

    public function getTemplateData(): array
    {
        return $this->templateData;
    }
}
