<?php

declare(strict_types=1);

namespace App\Application\Message\Organization;

class CreateOrganizationMessage
{
    public function __construct(
        public string $technicalId,
        public ?string $name,
        public ?string $emailAddress = null,
        public ?string $phoneNumber = null,
        public string $private = '',
        public ?string $street = null,
        public ?string $street2 = null,
        public ?string $manualZipCode = null,
        public ?string $manualCity = null,
    ) {
    }
}
