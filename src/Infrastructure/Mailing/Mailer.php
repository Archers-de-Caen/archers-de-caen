<?php

declare(strict_types=1);

namespace App\Infrastructure\Mailing;

use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class Mailer
{
    public function __construct(
        private readonly Environment $twig,
        private readonly MailerInterface $mailer,
        readonly private LoggerInterface $logger,
    ) {
    }

    /**
     * @throws EmailRenderingException
     */
    public function createEmail(string $template, array $templateData = []): Email
    {
        try {
            $this->twig->addGlobal('format', 'html');
            $html = $this->twig->render($template, array_merge($templateData, ['layout' => 'mails/base.html.twig']));

            $this->twig->addGlobal('format', 'text');
            $text = $this->twig->render($template, array_merge($templateData, ['layout' => 'mails/base.text.twig']));
        } catch (LoaderError|RuntimeError|SyntaxError $e) {
            $this->logger->error($e);

            throw new EmailRenderingException($e->getMessage(), $e->getCode(), $e);
        }

        return (new Email())
            ->from(new Address('noreply@archers-caen.fr', 'Archers de Caen'))
            ->addReplyTo(new Address('contact@archers-caen.fr', 'Archers de Caen'))
            ->html($html)
            ->text($text)
        ;
    }

    public function send(Email $email): void
    {
        try {
            $addresses = array_map(static fn (Address $address): string => $address->toString(), $email->getTo());
            $this->logger->info('Envoi d\'un email aux adresses : '.implode(',', $addresses));

            $this->mailer->send($email);
        } catch (TransportExceptionInterface $e) {
            $this->logger->error($e);
        }
    }
}
