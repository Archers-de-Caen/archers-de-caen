<?php

declare(strict_types=1);

namespace App\Domain\Result\Repository;

use App\Domain\Result\Model\ResultCompetition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ResultCompetition|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResultCompetition|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResultCompetition[]    findAll()
 * @method ResultCompetition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<ResultCompetition>
 */
final class ResultCompetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResultCompetition::class);
    }

    public function save(ResultCompetition $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ResultCompetition $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
