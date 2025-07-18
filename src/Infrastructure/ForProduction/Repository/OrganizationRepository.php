<?php

declare(strict_types=1);

namespace App\Infrastructure\ForProduction\Repository;

use App\Application\Port\IOrganizationRepository;
use App\Entity\Organization;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Organization>
 */
class OrganizationRepository extends ServiceEntityRepository implements IOrganizationRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Organization::class);
    }

    public function findByTechnicalId(string $technicalId): ?Organization
    {
        $query = $this->createQueryBuilder('o');
        $query->where('o.technicalId = :technicalId');
        $query->setParameter('technicalId', $technicalId);

        return $query->getQuery()->getOneOrNullResult();
    }

    public function findContactsNotUpdatedSinceWeek(int $dayForDeletion, \DateTimeImmutable $executionDatetime): array
    {
        $query = $this->createQueryBuilder('o')
            ->where('o.updatedAt < :executionDate')
            ->andWhere('o.deletedAt IS NULL')
            ->setParameter('executionDate', $executionDatetime->modify('-'.$dayForDeletion.' days'))
            ->getQuery();

        return $query->getResult();
    }

    public function delete(Organization $organization): void
    {
        $organization->deletedAt = new \DateTimeImmutable();
        $this->save($organization);
    }

    public function save(Organization $organization): void
    {
        $em = $this->getEntityManager();
        // TODO: Not performant - persist() is unnecessary for updates
        $em->persist($organization);
        $em->flush();
    }
}
