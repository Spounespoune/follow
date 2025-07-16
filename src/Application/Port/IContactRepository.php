<?php

declare(strict_types=1);

namespace App\Application\Port;

use App\Entity\Contact;

interface IContactRepository
{
    public function findById(int $id): ?Contact;

    public function save(Contact $contact): void;
}
