<?php

declare(strict_types=1);

namespace App\Infrastructure\Orm\EventListener;

use App\Helper\SecurityHelper;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

class TokenSubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    /**
     * @throws \Exception
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (property_exists($entity, 'token') && method_exists($entity, 'setToken')) {
            /** @var string $prefix */
            $prefix = \constant($entity::class.'::PREFIX_TOKEN');

            $entity->setToken(SecurityHelper::generateRandomToken(8, $prefix));
        }
    }
}
