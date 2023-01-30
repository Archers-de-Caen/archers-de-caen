<?php

declare(strict_types=1);

namespace App\Domain\Competition\Repository;

use App\Domain\Competition\Model\CompetitionRegister;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompetitionRegister|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionRegister|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionRegister[]    findAll()
 * @method CompetitionRegister[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<CompetitionRegister>
 */
final class CompetitionRegisterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompetitionRegister::class);
    }
}
