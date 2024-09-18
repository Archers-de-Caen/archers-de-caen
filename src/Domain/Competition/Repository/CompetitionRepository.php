<?php

declare(strict_types=1);

namespace App\Domain\Competition\Repository;

use App\Domain\Competition\Model\Competition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Competition|null find($id, $lockMode = null, $lockVersion = null)
 * @method Competition|null findOneBy(array $criteria, array $orderBy = null)
 * @method Competition[]    findAll()
 * @method Competition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Competition>
 */
final class CompetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competition::class);
    }

    public function save(Competition $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Competition $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array<string>
     */
    public function getAllLocations(): array
    {
        /* @phpstan-ignore-next-line */
        return $this->createQueryBuilder('competition')
            ->select('competition.location')
            ->distinct()
            ->orderBy('competition.location', 'ASC')
            ->getQuery()
            ->getSingleColumnResult()
        ;
    }

    /**
     * @return array<Competition>
     */
    public function findLastMonthCompetitions(): array
    {
        $now = new \DateTimeImmutable('now');
        $currentMonth = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-01 00:00:00'));

        if (false === $currentMonth) {
            throw new \RuntimeException('Cannot create date from format Y-m-01 00:00:00');
        }

        $lastMonth = $currentMonth->modify('-1 month');

        /** @var array<Competition> $competitions */
        $competitions = $this->createQueryBuilder('competition')
            ->where('competition.dateStart BETWEEN :lastMonth AND :currentMonth')
            ->setParameter('lastMonth', $lastMonth)
            ->setParameter('currentMonth', $currentMonth)
            ->getQuery()
            ->getResult()
        ;

        return $competitions;
    }
}
