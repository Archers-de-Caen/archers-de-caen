<?php

declare(strict_types=1);

namespace App\Domain\Webhook;

use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WebhookRepository::class)]
class Webhook
{
    use IdTrait;
    use TimestampTrait;

    public const SERVICE_HELLOASSO = 'helloasso';

    public const SERVICE_CHOICES = [
        self::SERVICE_HELLOASSO => self::SERVICE_HELLOASSO,
    ];

    #[ORM\Column(type: Types::STRING)]
    private ?string $type = null;

    #[ORM\Column(type: Types::STRING)]
    private ?string $service = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $reference = null;

    #[ORM\Column(type: Types::JSON)]
    private ?array $content = [];

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $result = null;

    #[\Override]
    public function __toString(): string
    {
        try {
            return json_encode($this->getContent(), \JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return $e->getMessage();
        }
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getService(): ?string
    {
        return $this->service;
    }

    public function setService(?string $service): self
    {
        $this->service = $service;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(?string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getContent(): ?array
    {
        return $this->content;
    }

    public function setContent(?array $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getResult(): ?string
    {
        return $this->result;
    }

    public function setResult(?string $result): self
    {
        $this->result = $result;

        return $this;
    }
}
