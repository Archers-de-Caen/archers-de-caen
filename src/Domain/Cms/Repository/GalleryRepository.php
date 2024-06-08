<?php

declare(strict_types=1);

namespace App\Domain\Cms\Repository;

use App\Domain\Cms\Model\Gallery;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Gallery|null find($id, $lockMode = null, $lockVersion = null)
 * @method Gallery|null findOneBy(array $criteria, array $orderBy = null)
 * @method Gallery[]    findAll()
 * @method Gallery[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Gallery>
 */
final class GalleryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Gallery::class);
    }

    public function save(Gallery $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Gallery $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array<Gallery>
     */
    public function findLastMonthGalleries(): array
    {
        $now = new \DateTimeImmutable('now');
        $currentMonth = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-01 00:00:00'));

        if (false === $currentMonth) {
            throw new \RuntimeException('Cannot create date from format Y-m-01 00:00:00');
        }

        $lastMonth = $currentMonth->modify('-1 month');

        /** @var array<Gallery> $galleries */
        $galleries = $this->createQueryBuilder('gallery')
            ->where('gallery.createdAt BETWEEN :lastMonth AND :currentMonth')
            ->setParameter('lastMonth', $lastMonth)
            ->setParameter('currentMonth', $currentMonth)
            ->getQuery()
            ->getResult();

        return $galleries;
    }
}
