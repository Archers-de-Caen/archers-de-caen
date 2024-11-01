<?php

declare(strict_types=1);

namespace App\Infrastructure\Mailing;

use App\Domain\File\Message\NewspaperAccessCreatedMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class NewspaperAccessCreatedHandler
{
    public function __construct(
        private Mailer $mailer,
    ) {
    }

    /**
     * @throws EmailRenderingException
     */
    public function __invoke(NewspaperAccessCreatedMessage $newspaperAccessCreatedMessage): void
    {
        $email = $this->mailer->createEmail(
            template: 'mails/newspaper/access.html.twig',
            templateData: [
                'password' => $newspaperAccessCreatedMessage->password,
            ],
        );

        $email
            ->subject('Voici votre accès aux gazettes')
            ->addTo($newspaperAccessCreatedMessage->email)
        ;

        $this->mailer->send($email);
    }
}
