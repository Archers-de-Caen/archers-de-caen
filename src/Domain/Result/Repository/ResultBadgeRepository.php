<?php

declare(strict_types=1);

namespace App\Domain\Result\Repository;

use App\Domain\Result\Model\ResultBadge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ResultBadge|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResultBadge|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResultBadge[]    findAll()
 * @method ResultBadge[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<ResultBadge>
 */
final class ResultBadgeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResultBadge::class);
    }

    public function save(ResultBadge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ResultBadge $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
