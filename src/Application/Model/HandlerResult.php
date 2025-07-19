<?php

declare(strict_types=1);

namespace App\Application\Model;

class HandlerResult
{
    public function __construct(
        public bool $success,
        public ?string $errorMessage = null,
    ) {
    }

    public static function success(): self
    {
        return new self(true, null);
    }

    public static function failure(string $errorMessage): self
    {
        return new self(false, $errorMessage);
    }
}
