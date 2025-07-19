<?php

declare(strict_types=1);

namespace App\Application\MessageHandler;

use App\Application\Message\Contact\UpdateContactMessage;
use App\Application\Model\HandlerResult;
use App\Application\Port\IContactRepository;
use App\Entity\Contact;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class UpdateContactHandler
{
    public function __construct(private IContactRepository $contactRepository)
    {
    }

    public function __invoke(UpdateContactMessage $contactMessage): HandlerResult
    {
        $contactPersist = $this->contactRepository->findByPpIdentifier($contactMessage->ppIdentifier);

        if (null === $contactPersist) {
            throw new \Exception('Contact not found');
        }

        $incomingContact = Contact::create(
            $contactMessage->ppIdentifier,
            $contactMessage->familyName,
            $contactPersist->getPpIdentifierType(), // ppIdentifier and ppIdentifierType are link, updated not possible
            $contactMessage->firstName,
            $contactMessage->title,
        );

        if ($contactPersist->identicalTo($incomingContact)) {
            return HandlerResult::success();
        }

        $this->updateContactFields($contactPersist, $incomingContact);

        try {
            $this->contactRepository->persist($contactPersist);
        } catch (\Exception $e) {
            return HandlerResult::failure($e->getMessage());
        }

        return HandlerResult::success();
    }

    public function updateContactFields(Contact $persisteContact, Contact $contact): void
    {
        if (null !== $contact->getFamilyName() && $persisteContact->getFamilyName() !== $contact->getFamilyName()) {
            $persisteContact->setFamilyName($contact->getFamilyName());
        }

        if (null !== $contact->getFirstName() && $persisteContact->getFirstName() !== $contact->getFirstName()) {
            $persisteContact->setFirstName($contact->getFirstName());
        }

        if (null !== $contact->getTitle() && $persisteContact->getTitle() !== $contact->getTitle()) {
            $persisteContact->setTitle($contact->getTitle());
        }

        if (null !== $persisteContact->deletedAt) {
            $persisteContact->deletedAt = null;
        }
    }
}
