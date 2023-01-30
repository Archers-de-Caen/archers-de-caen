<?php

declare(strict_types=1);

namespace App\Domain\Badge\Repository;

use App\Domain\Badge\Model\Badge;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Badge|null find($id, $lockMode = null, $lockVersion = null)
 * @method Badge|null findOneBy(array $criteria, array $orderBy = null)
 * @method Badge[]    findAll()
 * @method Badge[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Badge>
 */
final class BadgeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Badge::class);
    }

    /**
     * @return array<Badge>
     */
    public function findProgressArrow(): array
    {
        return $this->findBy([
           'type' => Badge::PROGRESS_ARROW,
        ], [
            'level' => Criteria::ASC,
        ]);
    }
}
