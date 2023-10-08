<?php

declare(strict_types=1);

namespace App\Domain\Cms\EventListener\Doctrine;

use App\Domain\Archer\Model\Archer;
use App\Domain\Cms\Model\Page;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: Page::class)]
class PageSubscriber
{
    public function __construct(private readonly Security $security)
    {
    }

    public function prePersist(Page $page, PrePersistEventArgs $event): void
    {
        /** @var Archer $archer */
        $archer = $this->security->getUser();

        if (!$page->getCreatedBy()) {
            $page->setCreatedBy($archer);
        }
    }
}
