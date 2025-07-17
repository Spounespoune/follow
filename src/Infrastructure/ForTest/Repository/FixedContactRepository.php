<?php

declare(strict_types=1);

namespace App\Infrastructure\ForTest\Repository;

use App\Application\Port\IContactRepository;
use App\Entity\Contact;

class FixedContactRepository implements IContactRepository
{
    public function __construct(private array $database = [])
    {
    }

    public function findByPpIdentifier(string $ppIdentifier): ?Contact
    {
        return array_find($this->database, fn (Contact $contact) => $contact->getPpIdentifier() === $ppIdentifier);
    }

    public function findContactsNotUpdatedSinceWeek(int $dayForDeletion, \DateTimeImmutable $executionDatetime): array
    {
        return array_filter($this->database, fn (Contact $contact) => $contact->updatedAt->modify('+'.$dayForDeletion.' days') <= $executionDatetime);
    }

    public function delete(Contact $contact): void
    {
        $contactInDatabase = array_find($this->database, fn (Contact $contact) => $contact->getPpIdentifier() === $contact->getPpIdentifier());
        $contactInDatabase->deletedAt = new \DateTimeImmutable();
    }

    public function save(Contact $contact): void
    {
        $this->database[] = $contact;
    }

    public function clear(): void
    {
        // Nothing
    }
}
