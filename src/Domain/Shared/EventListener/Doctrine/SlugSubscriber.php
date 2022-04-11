<?php

declare(strict_types=1);

namespace App\Domain\Shared\EventListener\Doctrine;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Exception;
use Symfony\Component\String\Slugger\SluggerInterface;

class SlugSubscriber implements EventSubscriberInterface
{
    public function __construct(private SluggerInterface $slugger, private EntityManagerInterface $em)
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

        if (
            property_exists($entity, 'slug') && method_exists($entity, 'setSlug') &&
            property_exists($entity, 'title') && method_exists($entity, 'getTitle')
        ) {
            $slug = $this->slugger->slug(strtolower($entity->getTitle()));
            $suffix = 0;

            while ($this->em->getRepository($entity::class)->findBy(['slug' => $slug.($suffix > 0 ? '-'.$suffix : '')])) {
                ++$suffix;
            }

            $entity->setSlug($slug.($suffix > 0 ? '-'.$suffix : ''));
        }
    }
}
