<?php

declare(strict_types=1);

namespace App\Domain\Archer\Repository;

use App\Domain\Archer\Model\ArcherLicense;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ArcherLicense|null find($id, $lockMode = null, $lockVersion = null)
 * @method ArcherLicense|null findOneBy(array $criteria, array $orderBy = null)
 * @method ArcherLicense[]    findAll()
 * @method ArcherLicense[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<ArcherLicense>
 */
final class ArcherLicenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArcherLicense::class);
    }

    public function save(ArcherLicense $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ArcherLicense $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
