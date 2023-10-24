<?php

declare(strict_types=1);

namespace App\Domain\Archer\EventListener;

use App\Domain\Archer\Model\Archer;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\EventDispatcher\Event;

#[AsEntityListener(event: Events::prePersist, method: 'hashPassword', entity: Archer::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'hashPassword', entity: Archer::class)]
class NewPasswordEvent
{
    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function hashPassword(Archer $archer): void
    {
        if ($plainPassword = $archer->getPlainPassword()) {
            $archer->setPassword($this->userPasswordHasher->hashPassword($archer, $plainPassword));
        }
    }
}
