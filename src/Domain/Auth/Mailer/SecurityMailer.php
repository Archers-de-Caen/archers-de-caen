<?php

declare(strict_types=1);

namespace App\Domain\Auth\Mailer;

use App\Domain\Archer\Model\Archer;
use App\Domain\Auth\Model\AuthToken;
use App\Infrastructure\Mailing\Mailer;
use Symfony\Component\Mime\Address;

class SecurityMailer extends Mailer
{
    public function sendConnectionLink(Archer $archer): void
    {
        if (!$archer->getEmail()) {
            return;
        }

        /** @var ?AuthToken $lastAuthToken */
        $lastAuthToken = $archer->getAuthTokens()->last();

        if (!$lastAuthToken) {
            $this->logger->error('Envoi email impossible, authToken inexistant pour l\'archer '.$archer->getUserIdentifier());

            return;
        }

        $email = $this->createEmail('/mails/security/connection-link.twig', [
            'token' => $lastAuthToken->getPlainToken(),
            'identifier' => $archer->getUserIdentifier(),
        ]);

        if (!$email) {
            return;
        }

        $email
            ->subject('Votre lien de connexion aux Archers de Caen')
            ->addTo(new Address($archer->getEmail(), $archer->getFirstName().' '.$archer->getLastName()));

        $this->send($email);
    }
}
