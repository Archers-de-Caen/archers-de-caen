<?php

declare(strict_types=1);

namespace App\Domain\Contact\Model;

use App\Domain\Contact\Config\Subject;
use App\Domain\Contact\Repository\ContactRequestRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\IpUtils;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Sauvegarde les demandes de contact afin de limiter le spam.
 */
#[ORM\Entity(ContactRequestRepository::class)]
class ContactRequest
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING, length: 191)]
    private string $ip = '';

    #[ORM\Column(type: Types::STRING, length: 191)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 3, max: 191)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 191)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private string $email;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 10)]
    private string $content;

    #[ORM\Column(type: Types::STRING, length: 191, enumType: Subject::class)]
    #[Assert\NotBlank]
    private Subject $subject;

    public function getIp(): string
    {
        return $this->ip;
    }

    public function setIp(string $ip): self
    {
        $this->ip = $ip;

        return $this;
    }

    public function setRawIp(?string $ip): self
    {
        if ($ip) {
            $this->ip = IPUtils::anonymize($ip);
        }

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(string $content): void
    {
        $this->content = $content;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function setSubject(Subject $subject): self
    {
        $this->subject = $subject;

        return $this;
    }
}
