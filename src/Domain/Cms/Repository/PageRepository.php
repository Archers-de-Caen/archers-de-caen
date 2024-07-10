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

    public function findSportPages(): array
    {
        $pages = $this->findByTagName('sport');

        $pagesSortByTags = [];

        foreach ($pages as $page) {
            $tagsName = [];
            foreach ($page->getTags() as $tag) {
                if (!$tag->getName()) {
                    continue;
                }

                if ('sport' === strtolower($tag->getName())) {
                    continue;
                }

                $tagsName[] = $tag->getName();
            }

            if ([] === $tagsName) {
                $tagsName[] = 'no-category';
            }

            if (!isset($pagesSortByTags[$tagsName[0]])) {
                $pagesSortByTags[$tagsName[0]] = [];
            }

            $pagesSortByTags[$tagsName[0]][] = $page;
        }

        return $pagesSortByTags;
    }
}
