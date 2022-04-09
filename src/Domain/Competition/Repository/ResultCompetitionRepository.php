<?php

declare(strict_types=1);

namespace App\Domain\Competition\Repository;

use App\Domain\Competition\Model\ResultCompetition;
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
class ResultCompetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResultCompetition::class);
    }
}
