<?php

declare(strict_types=1);

namespace App\Application\MessageHandler;

use App\Application\Message\Contact\UpdateContactMessage;
use App\Application\Port\IContactRepository;
use App\Entity\Contact;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class UpdateContactHandler
{
    public function __construct(private IContactRepository $contactRepository)
    {
    }

    public function __invoke(UpdateContactMessage $contactMessage): bool
    {
        $contactRecord = $this->contactRepository->findByPpIdentifier($contactMessage->ppIdentifier);

        if (null === $contactRecord) {
            throw new \Exception('Contact not found');
        }

        $contact = Contact::create(
            $contactMessage->ppIdentifier,
            $contactMessage->familyName,
            $contactMessage->ppIdentifierType,
            $contactMessage->title,
            $contactMessage->firstName,
        );

        if ($contactRecord->identicalTo($contact)) {
            return false;
        }

        if ($contactRecord->getFamilyName() !== $contact->getFamilyName()) {
            $contactRecord->setFamilyName($contact->getFamilyName());
        }

        if ($contactRecord->getFirstName() !== $contact->getFirstName()) {
            $contactRecord->setFirstName($contact->getFirstName());
        }

        if ($contactRecord->getTitle() !== $contact->getTitle()) {
            $contactRecord->setTitle($contact->getTitle());
        }

        if (null !== $contactRecord->deletedAt) {
            $contactRecord->deletedAt = null;
        }

        try {
            $this->contactRepository->save($contact);
        } catch (\Exception $e) {
            // TODO Log error
            return false;
        }

        return true;
    }
}
