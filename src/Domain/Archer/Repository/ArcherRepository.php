<?php

declare(strict_types=1);

namespace App\Domain\Archer\Repository;

use App\Domain\Archer\Model\Archer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Archer|null find($id, $lockMode = null, $lockVersion = null)
 * @method Archer|null findOneBy(array $criteria, array $orderBy = null)
 * @method Archer[]    findAll()
 * @method Archer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Archer>
 */
class ArcherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Archer::class);
    }

    public function loadUserByIdentifier(string $userIdentifier): ?Archer
    {
        try {
            /** @var ?Archer $archer */
            $archer = $this
                ->createQueryBuilder('archer')
                ->where('archer.email = :identifier')
                ->orWhere('archer.licenseNumber = :identifier')
                ->setParameter('identifier', $userIdentifier)
                ->getQuery()
                ->getOneOrNullResult();

            return $archer;
        } catch (NonUniqueResultException) {
            return null;
        }
    }
}
