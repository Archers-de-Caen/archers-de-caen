<?php

declare(strict_types=1);

namespace App\Infrastructure\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

trait TokenTrait
{
    #[ORM\Column(type: Types::STRING)]
    #[Groups(['Token'])]
    private ?string $token;

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): void
    {
        $this->token = $token;
    }
}
