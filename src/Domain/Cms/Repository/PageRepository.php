<?php

declare(strict_types=1);

namespace App\Domain\Cms\Repository;

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

    /**
     * @return array<Page>
     */
    public function findTagNameBy(string $tag): array
    {
        /** @var array<Page> $pages */
        $pages = $this->createQueryBuilder('page')
            ->leftJoin('page.tags', 'tags')
            ->where('tags.name = :tag')
            ->andWhere('tags.id IS NOT NULL')
            ->setParameter('tag', $tag)
            ->getQuery()
            ->getResult()
        ;

        return $pages;
    }
}
