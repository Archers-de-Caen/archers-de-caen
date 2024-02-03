<?php

declare(strict_types=1);

namespace App\Infrastructure\Orm\EventListener;

use App\Helper\SecurityHelper;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;

#[AsDoctrineListener(event: Events::prePersist)]
final class TokenSubscriber
{
    /**
     * @throws \Exception
     */
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if (property_exists($entity, 'token') && method_exists($entity, 'setToken')) {
            /** @var string $prefix */
            $prefix = \constant($entity::class.'::PREFIX_TOKEN');

            $entity->setToken(SecurityHelper::generateRandomToken(8, $prefix));
        }
    }
}
