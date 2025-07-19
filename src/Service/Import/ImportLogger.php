<?php

declare(strict_types=1);

namespace App\Service\Import;

use Psr\Log\LoggerInterface;

readonly class ImportLogger
{
    public function __construct(private LoggerInterface $importLogger)
    {
    }

    public function logError(string $itemName, int $i, ?string $itemId, string $errorMessage): void
    {
        $this->importLogger->error('Error while processing '.$itemName, [
            'line' => $i,
            'item_id' => $itemId ?? 'N/A',
            'error' => $errorMessage,
        ]);
    }
}
