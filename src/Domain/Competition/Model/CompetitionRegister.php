<?php

declare(strict_types=1);

namespace App\Domain\Competition\Model;

use App\Domain\Competition\Repository\CompetitionRegisterRepository;
use App\Infrastructure\Model\IdTrait;
use App\Infrastructure\Model\TimestampTrait;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CompetitionRegisterRepository::class)]
class CompetitionRegister
{
    use IdTrait;
    use TimestampTrait;
}
