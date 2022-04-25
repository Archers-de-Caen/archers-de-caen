<?php

namespace App\Domain\Contact\Service;

use App\Domain\Contact\Model\ContactRequest;
use App\Domain\Contact\Repository\ContactRequestRepository;
use App\Domain\Contact\TooManyContactException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class ContactService
{
    public function __construct(
        private readonly ContactRequestRepository $repository,
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        private readonly string $email,
        private readonly string $env
    ) {
    }

    /**
     * @throws TooManyContactException if the number of contact requests is too high (last message 1 hour ago)
     * @throws TransportExceptionInterface
     */
    public function send(ContactRequest $contactRequest, ?string $ip): void
    {
        $contactRequest->setRawIp($ip);

        $lastRequest = $this->repository->findLastRequestForIp($contactRequest->getIp());

        if ($lastRequest && $lastRequest->getCreatedAt() > new \DateTime('- 1 hour')) {
            throw new TooManyContactException();
        }

        $this->em->persist($contactRequest);

        $this->em->flush();

        $message = (new Email())
            ->text($contactRequest->getContent())
            ->subject("Site::Contact : {$contactRequest->getName()} : {$contactRequest->getSubject()->toString()}")
            ->from('noreply@archers-caen.fr')
            ->replyTo(new Address($contactRequest->getEmail(), $contactRequest->getName()))
            ->to($this->email);

        if ($this->env !== 'prod') {
            return;
        }

        $this->mailer->send($message);
    }
}
