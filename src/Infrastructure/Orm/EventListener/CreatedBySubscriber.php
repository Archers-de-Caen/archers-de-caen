<?php

declare(strict_types=1);

namespace App\Infrastructure\Orm\EventListener;

use App\Helper\SecurityHelper;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;
use Symfony\Component\Security\Core\Security;

class CreatedBySubscriber implements EventSubscriberInterface
{
    public function __construct(readonly private Security $security)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    /**
     * @throws Exception
     */
    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (property_exists($entity, 'createdBy') && method_exists($entity, 'setCreatedBy')) {
            $entity->setCreatedBy($this->security->getUser());
        }
    }
}
