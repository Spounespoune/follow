<?php

declare(strict_types=1);

namespace App\Tests\Units\MessageHandler;

use App\Application\Message\Contact\UpdateContactMessage;
use App\Application\MessageHandler\UpdateContactHandler;
use App\Entity\Contact;
use App\Infrastructure\ForTest\Repository\FixedContactRepository;
use PHPUnit\Framework\TestCase;

class UpdateContactHandlerTest extends TestCase
{
    private UpdateContactHandler $updateContactHandler;
    private FixedContactRepository $contactRepository;

    protected function setUp(): void
    {
        $contact = Contact::create(
            '10000001015',
            'family_name_test',
        );
        $this->contactRepository = new FixedContactRepository([$contact]);
        $this->updateContactHandler = new UpdateContactHandler($this->contactRepository);
    }

    public function testUpdateContact()
    {
        $updateContactMessage = new UpdateContactMessage(
            '10000001015',
            'family_name_test_update'
        );
        $updateContactUseCase = ($this->updateContactHandler)($updateContactMessage);

        $this->assertTrue($updateContactUseCase);

        /** @var Contact $contact */
        $contact = $this->contactRepository->findByPpIdentifier('10000001015');

        $this->assertNotNull($contact);
        $this->assertEquals('family_name_test_update', $contact->getFamilyName());
    }

    public function testUpdateContactWithAllFields()
    {
        $updateContactMessage = new UpdateContactMessage(
            '10000001015',
            'Updated Family Name',
            'Updated First Name',
            'Updated Title'
        );

        $updateContactUseCase = ($this->updateContactHandler)($updateContactMessage);

        $this->assertTrue($updateContactUseCase);

        /** @var Contact $contact */
        $contact = $this->contactRepository->findByPpIdentifier('10000001015');

        $this->assertNotNull($contact);
        $this->assertEquals('Updated Family Name', $contact->getFamilyName());
        $this->assertEquals('Updated First Name', $contact->getFirstName());
        $this->assertEquals('Updated Title', $contact->getTitle());
        $this->assertEquals('10000001015', $contact->getPpIdentifier());
    }
}
