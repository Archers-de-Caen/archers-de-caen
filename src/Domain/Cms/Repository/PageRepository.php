<?php

declare(strict_types=1);

namespace App\Domain\Cms\Repository;

use App\Domain\Cms\Config\Category;
use App\Domain\Cms\Model\Page;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Page>
 */
final class PageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
    }

    public function save(Page $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Page $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return array<Page>
     */
    public function findByTagName(string $tag): array
    {
        /** @var array<Page> $pages */
        $pages = $this->createQueryBuilder('page')
            ->leftJoin('page.tags', 'tags')

            ->where('tags.name = :tag')
            ->setParameter('tag', $tag)

            ->andWhere('tags.id IS NOT NULL')

            ->getQuery()
            ->getResult()
        ;

        return $pages;
    }

    /**
     * @return array<Page>
     */
    public function findLastMonthActualities(): array
    {
        $now = new \DateTimeImmutable('now');
        $currentMonth = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $now->format('Y-m-01 00:00:00'));

        if (false === $currentMonth) {
            throw new \RuntimeException('Cannot create date from format Y-m-01 00:00:00');
        }

        $lastMonth = $currentMonth->modify('-1 month');

        /** @var array<Page> $actualities */
        $actualities = $this->createQueryBuilder('page')
            ->where('page.category = :category')
            ->setParameter('category', Category::ACTUALITY)

            ->andWhere('page.createdAt BETWEEN :lastMonth AND :currentMonth')
            ->setParameter('lastMonth', $lastMonth)
            ->setParameter('currentMonth', $currentMonth)

            ->getQuery()
            ->getResult()
        ;

        return $actualities;
    }
}
