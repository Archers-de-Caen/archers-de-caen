<?php

declare(strict_types=1);

namespace App\Domain\Auth\Model;

use App\Domain\Archer\Model\Archer;
use App\Domain\Auth\Repository\AuthTokenRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuthTokenRepository::class)]
class AuthToken
{
    use IdTrait;
    use TimestampTrait;

    #[ORM\ManyToOne(targetEntity: Archer::class, inversedBy: 'authTokens')]
    private ?Archer $archer = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $usedAt = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $expiredAt = null;

    #[ORM\Column(type: Types::TEXT, length: 191)]
    private ?string $token = null;

    private string $plainToken;

    public function getArcher(): ?Archer
    {
        return $this->archer;
    }

    public function setArcher(?Archer $archer): self
    {
        $this->archer = $archer;

        return $this;
    }

    public function getUsedAt(): ?\DateTimeImmutable
    {
        return $this->usedAt;
    }

    public function setUsedAt(?\DateTimeImmutable $usedAt): self
    {
        $this->usedAt = $usedAt;

        return $this;
    }

    public function getExpiredAt(): ?\DateTimeImmutable
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(?\DateTimeImmutable $expiredAt): self
    {
        $this->expiredAt = $expiredAt;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getPlainToken(): string
    {
        return $this->plainToken;
    }

    public function setPlainToken(string $plainToken): self
    {
        $this->plainToken = $plainToken;

        return $this;
    }
}
