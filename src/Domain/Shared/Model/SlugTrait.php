<?php

declare(strict_types=1);

namespace App\Domain\Shared\Model;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

#[UniqueEntity(['slug'])]
trait SlugTrait
{
    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    #[Groups(['Token'])]
    private ?string $slug = null;

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(?string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }
}
