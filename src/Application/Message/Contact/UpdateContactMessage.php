<?php

declare(strict_types=1);

namespace App\Application\Message\Contact;

class UpdateContactMessage
{
    public function __construct(
        public string $ppIdentifier,
        public ?string $familyName,
        public ?string $firstName = null,
        public ?string $ppIdentifierType = null,
        public ?string $title = null,
    ) {
    }
}
