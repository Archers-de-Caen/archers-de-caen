<?php

declare(strict_types=1);

namespace App\Domain\Competition\Repository;

use App\Domain\Competition\Model\Trapta;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Trapta|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trapta|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trapta[]    findAll()
 * @method Trapta[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Trapta>
 */
class TraptaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trapta::class);
    }

    public function save(Trapta $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Trapta $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
