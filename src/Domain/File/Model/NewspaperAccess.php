<?php

declare(strict_types=1);

namespace App\Domain\File\Model;

use App\Domain\File\Repository\NewspaperAccessRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: NewspaperAccessRepository::class)]
class NewspaperAccess
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(type: Types::STRING, length: 191)]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING, length: 191)]
    private ?string $password = null;

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }
}
