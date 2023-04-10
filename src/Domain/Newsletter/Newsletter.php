<?php

declare(strict_types=1);

namespace App\Domain\Newsletter;

use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Historique d'envoi newsletter.
 */
#[ORM\Entity(NewsletterRepository::class)]
class Newsletter
{
    use IdTrait;
    use TimestampTrait;

    /**
     * @var array<string>
     */
    #[ORM\Column(type: Types::SIMPLE_ARRAY, nullable: true)]
    private array $emails;

    #[ORM\Column(type: Types::JSON)]
    private array $context;

    #[ORM\Column(type: Types::STRING, length: 191, enumType: NewsletterType::class)]
    private NewsletterType $type;

    public function getEmails(): array
    {
        return $this->emails;
    }

    public function setEmails(array $emails): self
    {
        $this->emails = $emails;

        return $this;
    }

    public function addEmail(string $email): self
    {
        if (!\in_array($email, $this->emails, true)) {
            $this->emails[] = $email;
        }

        return $this;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function setContext(array $context): self
    {
        $this->context = $context;

        return $this;
    }

    public function getType(): NewsletterType
    {
        return $this->type;
    }

    public function setType(NewsletterType $type): self
    {
        $this->type = $type;

        return $this;
    }
}
