<?php

declare(strict_types=1);

namespace App\Infrastructure\Mailing;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class AdminNotificationHandler
{
    public function __construct(
        private Mailer $mailer,
    ) {
    }

    /**
     * @throws EmailRenderingException
     */
    public function __invoke(AdminNotificationMessage $adminNotificationMessage): void
    {
        $email = $this->mailer->createEmail(
            $adminNotificationMessage->getTemplatePath(),
            $adminNotificationMessage->getTemplateData(),
        );

        $emails = [
            'dev@archers-caen.fr',
        ];

        $email
            ->subject($adminNotificationMessage->getSubject())
            ->addTo(...$emails)
        ;

        $this->mailer->send($email);
    }
}
