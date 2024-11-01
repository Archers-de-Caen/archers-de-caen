<?php

declare(strict_types=1);

namespace App\Domain\File\Repository;

use App\Domain\File\Model\NewspaperAccess;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method NewspaperAccess|null find($id, $lockMode = null, $lockVersion = null)
 * @method NewspaperAccess|null findOneBy(array $criteria, array $orderBy = null)
 * @method NewspaperAccess[]    findAll()
 * @method NewspaperAccess[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<NewspaperAccess>
 */
final class NewspaperAccessRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NewspaperAccess::class);
    }

    public function save(NewspaperAccess $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(NewspaperAccess $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
