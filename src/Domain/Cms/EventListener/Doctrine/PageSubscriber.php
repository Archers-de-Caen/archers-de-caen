<?php

declare(strict_types=1);

namespace App\Domain\Cms\EventListener\Doctrine;

use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Model\Page;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\Security;

class PageSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly Security $security)
    {
    }

    public function getSubscribedEvents(): array
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Page) {
            return;
        }

        // Set createdBy
        /** @var Archer $archer */
        $archer = $this->security->getUser();

        if (!$entity->getCreatedBy()) {
            $entity->setCreatedBy($archer);
        }
    }
}
