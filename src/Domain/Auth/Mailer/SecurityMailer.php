<?php

declare(strict_types=1);

namespace App\Domain\Auth\Mailer;

use App\Domain\Archer\Model\Archer;
use App\Infrastructure\Mailing\Mailer;
use Symfony\Component\Mime\Address;

class SecurityMailer extends Mailer
{
    public function sendConnectionLink(Archer $archer): void
    {
        if (!$archer->getEmail()) {
            return;
        }

        $email = $this->createEmail('/mails/security/connection-link.twig');

        if (!$email) {
            return;
        }

        $email
            ->subject('Votre lien de connexion aux Archers de Caen')
            ->addTo(new Address($archer->getEmail(), $archer->getFirstName().' '.$archer->getLastName()));

        $this->send($email);
    }
}
