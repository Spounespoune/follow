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

    public function findById(int $id): ?Contact
    {
        return array_find($this->database, fn ($contact) => $contact->getId() === $id);
    }

    public function save(Contact $contact): void
    {
        $id = count($this->database) + 1;
        $contact->setId($id);

        $this->database[] = $contact;
    }
}
