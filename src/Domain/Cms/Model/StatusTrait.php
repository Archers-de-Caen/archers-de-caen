<?php

declare(strict_types=1);

namespace App\Domain\Cms\Model;

use App\Domain\Cms\Config\Status;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

trait StatusTrait
{
    #[ORM\Column(type: Types::STRING, length: 191, enumType: Status::class, options: ['default' => Status::DRAFT])]
    #[Assert\NotNull]
    private ?Status $status = Status::DRAFT;

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function publish(): self
    {
        $this->setStatus(Status::PUBLISH);

        return $this;
    }
}
