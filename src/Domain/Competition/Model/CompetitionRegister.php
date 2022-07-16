<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Competition\Config\Type;
use App\Domain\Competition\Repository\CompetitionRegisterRepository;
use App\Domain\File\Model\Document;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CompetitionRegisterRepository::class)]
class CompetitionRegister
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $dateStart = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?DateTimeImmutable $dateEnd = null;

    #[ORM\Column(type: Types::STRING, length: 191, enumType: Type::class)]
    #[Assert\NotNull]
    private ?Type $type = null;

    #[ORM\Column(type: Types::JSON)]
    private array $departure = [];

    #[ORM\OneToOne(targetEntity: Document::class)]
    private ?Document $mandate = null;

    public function getDateStart(): ?DateTimeImmutable
    {
        return $this->dateStart;
    }

    public function setDateStart(?DateTimeImmutable $dateStart): self
    {
        $this->dateStart = $dateStart;

        return $this;
    }

    public function getDateEnd(): ?DateTimeImmutable
    {
        return $this->dateEnd;
    }

    public function setDateEnd(?DateTimeImmutable $dateEnd): self
    {
        $this->dateEnd = $dateEnd;

        return $this;
    }

    public function getType(): ?Type
    {
        return $this->type;
    }

    public function setType(?Type $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getMandate(): ?Document
    {
        return $this->mandate;
    }

    public function setMandate(?Document $mandate): void
    {
        $this->mandate = $mandate;
    }

    public function getDeparture(): array
    {
        return $this->departure;
    }

    public function setDeparture(array $departure): void
    {
        $this->departure = $departure;
    }
}
