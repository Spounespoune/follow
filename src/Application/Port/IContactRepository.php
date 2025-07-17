<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Entity\Contact;

interface IContactRepository
{
    public function findByPpIdentifier(string $ppIdentifier): ?Contact;

    public function findContactsNotUpdatedSinceWeek(int $dayForDeletion, \DateTimeImmutable $executionDatetime): array;

    public function delete(Contact $contact): void;

    public function save(Contact $contact): void;

    public function clear(): void;
}
