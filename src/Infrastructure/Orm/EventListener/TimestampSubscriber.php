<?php

declare(strict_types=1);

namespace App\Infrastructure\Orm\EventListener;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

final class TimestampSubscriber implements EventSubscriberInterface
{
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

        if (method_exists($entity, 'setCreatedAt')) {
            $entity->setCreatedAt(new \DateTime());
        }
    }

    public function preUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (property_exists($entity, 'updatedAt') && method_exists($entity, 'setUpdatedAt')) {
            $entity->setUpdatedAt(new \DateTime());
        }
    }
}
