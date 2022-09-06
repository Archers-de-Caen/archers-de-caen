<?php

declare(strict_types=1);

namespace App\Domain\License\Repository;

use App\Domain\License\Model\ArcherLicense;
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
class ArcherLicenseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ArcherLicense::class);
    }
}
