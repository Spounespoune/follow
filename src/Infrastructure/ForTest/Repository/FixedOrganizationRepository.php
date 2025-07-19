<?php

declare(strict_types=1);

namespace App\Infrastructure\ForTest\Repository;

use App\Application\Port\IOrganizationRepository;
use App\Entity\Organization;

class FixedOrganizationRepository implements IOrganizationRepository
{
    public function __construct(private array $database = [])
    {
    }

    public function findByTechnicalId(string $technicalId): ?Organization
    {
        return array_find($this->database, fn (Organization $organization) => $organization->getTechnicalId() === $technicalId);
    }

    public function findContactsNotUpdatedSinceWeek(int $dayForDeletion, \DateTimeImmutable $executionDatetime): array
    {
        return array_filter($this->database, fn (Organization $organization) => $organization->updatedAt->modify('+'.$dayForDeletion.' days') <= $executionDatetime);
    }

    public function save(Organization $organization): void
    {
        $this->persist($organization);
        $this->flush();
    }

    public function persist(Organization $organization): void
    {
        $this->database[] = $organization;
    }

    public function flush(): void
    {
        // Noting
    }
}
