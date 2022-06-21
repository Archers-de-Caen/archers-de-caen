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
class ContactRequestRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ContactRequest::class);
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
        } catch (NonUniqueResultException $e) {
        }

        return null;
    }
}
