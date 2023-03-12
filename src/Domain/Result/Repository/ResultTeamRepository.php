<?php

declare(strict_types=1);

namespace App\Domain\Result\Repository;

use App\Domain\Result\Model\ResultTeam;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ResultTeam|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResultTeam|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResultTeam[]    findAll()
 * @method ResultTeam[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<ResultTeam>
 */
class ResultTeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResultTeam::class);
    }
}
