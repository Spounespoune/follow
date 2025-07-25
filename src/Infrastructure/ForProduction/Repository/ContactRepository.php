<?php

declare(strict_types=1);

namespace App\Infrastructure\ForProduction\Repository;

use App\Application\Port\IContactRepository;
use App\Entity\Contact;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Contact>
 */
class ContactRepository extends ServiceEntityRepository implements IContactRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Contact::class);
    }

    public function findByPpIdentifier(string $ppIdentifier): ?Contact
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.ppIdentifier = :ppIdentifier')
            ->setParameter('ppIdentifier', $ppIdentifier)
            ->getQuery();

        return $query->getOneOrNullResult();
    }

    public function findContactsNotUpdatedSinceWeek(int $dayForDeletion, \DateTimeImmutable $executionDatetime): array
    {
        $query = $this->createQueryBuilder('c')
            ->where('c.updatedAt < :executionDate')
            ->andWhere('c.deletedAt IS NULL')
            ->setParameter('executionDate', $executionDatetime->modify('-'.$dayForDeletion.' days'))
            ->getQuery();

        return $query->getResult();
    }

    public function delete(Contact $contact): void
    {
        $contact->deletedAt = new \DateTimeImmutable();
        $this->flush();
    }

    public function save(Contact $contact): void
    {
        $this->persist($contact);
        $this->flush();
    }

    public function persist(Contact $contact): void
    {
        $this->getEntityManager()->persist($contact);
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function clear(): void
    {
        $this->getEntityManager()->clear();
    }
}
