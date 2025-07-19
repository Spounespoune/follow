<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Entity\Contact;
use App\Entity\Organization;

interface IOrganizationRepository
{
    public function findByTechnicalId(string $technicalId): ?Organization;

    public function findContactsNotUpdatedSinceWeek(int $dayForDeletion, \DateTimeImmutable $executionDatetime): array;

    public function save(Organization $organization): void;

    public function persist(Organization $contact): void;

    public function flush(): void;
}
