<?php

declare(strict_types=1);

namespace App\Application\Message\Organization;

readonly class DeleteOrganizationMessage
{
    public function __construct(
        public int $dayForDeletion = 7,
        public \DateTimeImmutable $executionDate = new \DateTimeImmutable(),
    ) {
    }
}
