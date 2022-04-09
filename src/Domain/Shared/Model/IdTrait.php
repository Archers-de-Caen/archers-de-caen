<?php

declare(strict_types=1);

namespace App\Domain\Shared\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

trait IdTrait
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id;

    public function getId(): ?int
    {
        return $this->id;
    }
}
