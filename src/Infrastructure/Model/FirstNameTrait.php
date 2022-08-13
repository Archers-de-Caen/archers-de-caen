<?php

declare(strict_types=1);

namespace App\Infrastructure\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait FirstNameTrait
{
    #[ORM\Column(type: Types::STRING, length: 191)]
    #[Assert\Length(max: 191)]
    #[Assert\NotBlank]
    #[Assert\NotNull]
    private ?string $firstName = null;

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }
}
