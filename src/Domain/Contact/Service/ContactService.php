<?php

declare(strict_types=1);

namespace App\Domain\Contact\Service;

use App\Domain\Contact\Config\Subject;
use App\Domain\Contact\Model\ContactRequest;
use App\Domain\Contact\Repository\ContactRequestRepository;
use App\Domain\Contact\TooManyContactException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

use function Symfony\Component\Translation\t;

class ContactService
{
    public function __construct(
        private readonly ContactRequestRepository $repository,
        private readonly EntityManagerInterface $em,
        private readonly MailerInterface $mailer,
        private readonly string $email,
        private readonly string $emailSite,
        private readonly string $env
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

        if ($lastRequest instanceof \App\Domain\Contact\Model\ContactRequest && $lastRequest->getCreatedAt() > new \DateTime('- 1 hour')) {
            throw new TooManyContactException();
        }

        $this->em->persist($contactRequest);

        $this->em->flush();

        $email = $this->email;
        if (Subject::WEB_SITE === $contactRequest->getSubject()) {
            $email = $this->emailSite;
        }

        $message = (new Email())
            ->text($contactRequest->getContent())
            ->subject("Site::Contact : {$contactRequest->getName()} : ".t($contactRequest->getSubject()->value, domain: 'mail'))
            ->from('noreply@archers-caen.fr')
            ->replyTo(new Address($contactRequest->getEmail(), $contactRequest->getName()))
            ->to($email);

        if ('prod' !== $this->env) {
            return;
        }

        $this->mailer->send($message);
    }
}
