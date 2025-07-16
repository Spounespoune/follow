<?php

declare(strict_types=1);

namespace App\Application\Message\Contact;

class ContactMessage
{
    public function __construct(
        public string $familyName,
        public string $ppIdentifier,
        public ?string $firstName = null,
        public ?string $ppIdentifierType = null,
        public ?string $title = null,
    ) {
    }
}
