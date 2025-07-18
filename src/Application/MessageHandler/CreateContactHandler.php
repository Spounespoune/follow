<?php

declare(strict_types=1);

namespace App\Application\MessageHandler;

use App\Application\Message\Contact\CreateContactMessage;
use App\Application\Port\IContactRepository;
use App\Entity\Contact;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CreateContactHandler
{
    public function __construct(private IContactRepository $contactRepository)
    {
    }

    public function __invoke(CreateContactMessage $contactMessage): bool
    {
        $contact = new Contact();
        $contact
            ->setFamilyName($contactMessage->familyName)
            ->setPpIdentifier($contactMessage->ppIdentifier)
            ->setFirstName($contactMessage->firstName)
            ->setPpIdentifierType((int) $contactMessage->ppIdentifierType)
            ->setTitle($contactMessage->title)
        ;

        try {
            $this->contactRepository->save($contact);
            $this->contactRepository->clear();
        } catch (\Exception $e) {
            // TODO Log error
            return false;
        }

        return true;
    }
}
