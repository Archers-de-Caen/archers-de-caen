<?php

declare(strict_types=1);

namespace App\Domain\Competition\Repository;

use App\Domain\Competition\Model\ResultProgressArrow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ResultProgressArrow|null find($id, $lockMode = null, $lockVersion = null)
 * @method ResultProgressArrow|null findOneBy(array $criteria, array $orderBy = null)
 * @method ResultProgressArrow[]    findAll()
 * @method ResultProgressArrow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<ResultProgressArrow>
 */
class ResultProgressArrowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ResultProgressArrow::class);
    }
}
