<?php

declare(strict_types=1);

namespace App\Domain\Competition\Repository;

use App\Domain\Competition\Model\ProgressArrow;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method ProgressArrow|null find($id, $lockMode = null, $lockVersion = null)
 * @method ProgressArrow|null findOneBy(array $criteria, array $orderBy = null)
 * @method ProgressArrow[]    findAll()
 * @method ProgressArrow[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<ProgressArrow>
 */
class ProgressArrowRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ProgressArrow::class);
    }
}
