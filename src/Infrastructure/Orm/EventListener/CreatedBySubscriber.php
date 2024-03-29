<?php

declare(strict_types=1);

namespace App\Infrastructure\Orm\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

#[AsDoctrineListener(event: Events::prePersist)]
final class CreatedBySubscriber
{
    public function __construct(readonly private Security $security)
    {
    }

    /**
     * @throws \Exception
     */
    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        if (!property_exists($entity, 'createdBy')) {
            return;
        }

        if (!method_exists($entity, 'setCreatedBy')) {
            return;
        }

        if (!$this->security->getUser() instanceof UserInterface) {
            return;
        }

        $entity->setCreatedBy($this->security->getUser());
    }
}
