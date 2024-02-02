<?php

declare(strict_types=1);

namespace App\Domain\Contact\Repository;

use App\Domain\Contact\Model\ContactRequest;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ContactRequest>
 */
final class ContactRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactRequest::class);
    }

    public function save(ContactRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ContactRequest $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findLastRequestForIp(string $ip): ?ContactRequest
    {
        try {
            /** @var ContactRequest $contact */
            $contact = $this->createQueryBuilder('req')
                ->where('req.ip = :ip')
                ->setParameter('ip', $ip)
                ->orderBy('req.createdAt')
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            return $contact;
        } catch (NonUniqueResultException $nonUniqueResultException) {
        }

        return null;
    }
}
