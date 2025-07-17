<?php

declare(strict_types=1);

namespace App\Application\MessageHandler;

use App\Application\Message\Contact\DeleteContactMessage;
use App\Application\Port\IContactRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class DeleteContactHandler
{
    public function __construct(private IContactRepository $contactRepository)
    {
    }

    public function __invoke(DeleteContactMessage $contactMessage): int
    {
        $countContactDeleted = 0;

        $contactToDelete = $this->contactRepository->findContactsNotUpdatedSinceWeek(
            $contactMessage->dayForDeletion,
            $contactMessage->executionDate,
        );

        foreach ($contactToDelete as $contact) {
            $this->contactRepository->delete($contact);
            ++$countContactDeleted;
        }

        return $countContactDeleted;
    }
}
