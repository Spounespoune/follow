<?php

declare(strict_types=1);

namespace App\Entity;

interface HashableInterface
{
    public function hash(): string;

    public function identicalTo(HashableInterface $obj): bool;
}
