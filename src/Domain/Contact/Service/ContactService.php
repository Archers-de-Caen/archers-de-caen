<?php

declare(strict_types=1);

namespace App\Domain\Contact\Service;

use App\Domain\Contact\Model\ContactRequest;
use App\Domain\Contact\Repository\ContactRequestRepository;
use App\Domain\Contact\TooManyContactException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

use function Symfony\Component\Translation\t;

final readonly class ContactService
{
    public function __construct(
        private ContactRequestRepository $repository,
        private EntityManagerInterface $em,
        private MailerInterface $mailer,
        private string $email,
    ) {
    }

    /**
     * @throws TooManyContactException     if the number of contact requests is too high (last message 1 hour ago)
     * @throws TransportExceptionInterface
     */
    public function send(ContactRequest $contactRequest, ?string $ip): void
    {
        $contactRequest->setRawIp($ip);

        $lastRequest = $this->repository->findLastRequestForIp($contactRequest->getIp());

        if ($lastRequest instanceof ContactRequest && $lastRequest->getCreatedAt() > new \DateTime('- 1 hour')) {
            throw new TooManyContactException();
        }

        $this->em->persist($contactRequest);

        $this->em->flush();

        $message = (new Email())
            ->text($contactRequest->getContent())
            ->subject(\sprintf('Site::Contact : %s : ', $contactRequest->getName()).t($contactRequest->getSubject()->value, domain: 'mail'))
            ->from('noreply@archers-caen.fr')
            ->replyTo(new Address($contactRequest->getEmail(), $contactRequest->getName()))
            ->to($this->email);

        $this->mailer->send($message);
    }
}
