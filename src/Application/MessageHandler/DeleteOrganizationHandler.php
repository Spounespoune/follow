<?php

declare(strict_types=1);

namespace App\Application\MessageHandler;

use App\Application\Message\Contact\DeleteContactMessage;
use App\Application\Port\IOrganizationRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class DeleteOrganizationHandler
{
    public function __construct(private IOrganizationRepository $organizationRepository)
    {
    }

    public function __invoke(DeleteContactMessage $contactMessage): int
    {
        $countOrganizationDeleted = 0;

        $organizationToDelete = $this->organizationRepository->findContactsNotUpdatedSinceWeek(
            $contactMessage->dayForDeletion,
            $contactMessage->executionDate,
        );

        foreach ($organizationToDelete as $organization) {
            $this->organizationRepository->delete($organization);
            ++$countOrganizationDeleted;
        }

        return $countOrganizationDeleted;
    }
}
