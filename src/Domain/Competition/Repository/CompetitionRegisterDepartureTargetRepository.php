<?php

declare(strict_types=1);

namespace App\Domain\Competition\Repository;

use App\Domain\Competition\Model\CompetitionRegisterDepartureTarget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompetitionRegisterDepartureTarget|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionRegisterDepartureTarget|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionRegisterDepartureTarget[]    findAll()
 * @method CompetitionRegisterDepartureTarget[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<CompetitionRegisterDepartureTarget>
 */
final class CompetitionRegisterDepartureTargetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompetitionRegisterDepartureTarget::class);
    }

    public function save(CompetitionRegisterDepartureTarget $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(CompetitionRegisterDepartureTarget $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
