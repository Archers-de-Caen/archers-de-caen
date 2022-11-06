<?php

declare(strict_types=1);

namespace App\Domain\Competition\Repository;

use App\Domain\Competition\Model\CompetitionRegister;
use App\Domain\Competition\Model\CompetitionRegisterDepartureTargetArcher;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method CompetitionRegisterDepartureTargetArcher|null find($id, $lockMode = null, $lockVersion = null)
 * @method CompetitionRegisterDepartureTargetArcher|null findOneBy(array $criteria, array $orderBy = null)
 * @method CompetitionRegisterDepartureTargetArcher[]    findAll()
 * @method CompetitionRegisterDepartureTargetArcher[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<CompetitionRegisterDepartureTargetArcher>
 */
class CompetitionRegisterDepartureTargetArcherRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CompetitionRegisterDepartureTargetArcher::class);
    }

    public function findOneArcherByLicenseNumber(string $licenseNumber): ?CompetitionRegisterDepartureTargetArcher
    {
        /** @var CompetitionRegisterDepartureTargetArcher[] $results */
        $results = $this->createQueryBuilder('crdta')
            ->where('crdta.licenseNumber = :licenseNumber')
            ->setParameter('licenseNumber', $licenseNumber)
            ->orderBy('crdta.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        return $results[0] ?? null;
    }

    /**
     * @return array<CompetitionRegisterDepartureTargetArcher>
     */
    public function findByCompetitionRegisterAndLicenseNumber(
        CompetitionRegister $competitionRegister,
        string $licenseNumber
    ): array {
        $query = $this
            ->createQueryBuilder('crdta')
            ->leftJoin('crdta.target', 'target')
            ->leftJoin('target.departure', 'departure')
            ->leftJoin('departure.competitionRegister', 'competitionRegister')
            ->where('competitionRegister.slug = :competitionRegister')
            ->andWhere('crdta.licenseNumber = :licenseNumber')
            ->setParameter('competitionRegister', $competitionRegister->getSlug())
            ->setParameter('licenseNumber', $licenseNumber)
            ->getQuery()
        ;

        /** @var array<CompetitionRegisterDepartureTargetArcher> $result */
        $result = $query->getResult();

        return $result;
    }
}
