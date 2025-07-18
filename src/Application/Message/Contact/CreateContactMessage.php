<?php

declare(strict_types=1);

namespace App\Application\Message\Contact;

class CreateContactMessage
{
    public function __construct(
        public string $ppIdentifier,
        public ?string $familyName,
        public ?string $firstName = null,
        public ?int $ppIdentifierType = null,
        public ?string $title = null,
    ) {
    }
}
