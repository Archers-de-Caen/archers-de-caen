<?php

declare(strict_types=1);

namespace App\Infrastructure\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait EmailTrait
{
    #[ORM\Column(type: Types::STRING, length: 191, unique: self::EMAIL_UNIQUE, nullable: true)]
    #[Assert\Length(max: 191)]
    #[Assert\Email]
    private ?string $email = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
