<?php

declare(strict_types=1);

namespace App\Domain\Archer\Repository;

use App\Domain\Archer\Model\Archer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Archer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Archer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Archer[]    findAll()
 * @method Archer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Archer>
 */
final class ArcherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Archer::class);
    }

    public function save(Archer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Archer $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
