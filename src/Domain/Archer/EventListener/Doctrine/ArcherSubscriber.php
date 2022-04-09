<?php

declare(strict_types=1);

namespace App\Domain\Archer\EventListener\Doctrine;

use App\Domain\Archer\Model\Archer;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ArcherSubscriber implements EventSubscriberInterface
{
    public function __construct(private UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Archer) {
            return;
        }

        $this->common($entity);
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Archer) {
            return;
        }

        $this->common($entity);
    }

    private function common(Archer $archer): void
    {
        if ($plainPassword = $archer->getPlainPassword()) {
            $archer->setPassword($this->userPasswordHasher->hashPassword($archer, $plainPassword));
        }
    }
}
