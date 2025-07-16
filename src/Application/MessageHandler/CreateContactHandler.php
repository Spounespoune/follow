<?php

declare(strict_types=1);

namespace App\Application\MessageHandler;

use App\Application\Message\Contact\ContactMessage;
use App\Application\Port\IContactRepository;
use App\Entity\Contact;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CreateContactHandler
{
    public function __construct(private IContactRepository $contactRepository)
    {
    }

    public function __invoke(ContactMessage $contactMessage): void
    {
        $contact = new Contact();
        $contact
            ->setFamilyName($contactMessage->familyName)
            ->setPpIdentifier($contactMessage->ppIdentifier)
            ->setFirstName($contactMessage->firstName)
            ->setPpIdentifierType($contactMessage->ppIdentifierType)
            ->setTitle($contactMessage->title)
        ;

        $this->contactRepository->save($contact);
    }
}
