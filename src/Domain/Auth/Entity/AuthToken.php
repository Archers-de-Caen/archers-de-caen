<?php

declare(strict_types=1);

namespace App\Domain\Auth\Entity;

use App\Domain\Auth\Repository\AuthTokenRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuthTokenRepository::class)]
class AuthToken
{
    use IdTrait;
    use TimestampTrait;
}
