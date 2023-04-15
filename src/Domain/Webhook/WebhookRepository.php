<?php

declare(strict_types=1);

namespace App\Domain\Webhook;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Webhook|null find($id, $lockMode = null, $lockVersion = null)
 * @method Webhook|null findOneBy(array $criteria, array $orderBy = null)
 * @method Webhook[]    findAll()
 * @method Webhook[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class WebhookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Webhook::class);
    }

    public function save(Webhook $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Webhook $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
