<?php

namespace Units\MessageHandler;

use App\Application\Message\Contact\ContactMessage;
use App\Application\MessageHandler\CreateContactHandler;
use App\Entity\Contact;
use App\Infrastructure\ForTest\Repository\FixedContactRepository;
use PHPUnit\Framework\TestCase;

class createContactHandlerTest extends TestCase
{
    private CreateContactHandler $createContactUseCase;
    private FixedContactRepository $contactRepository;

    protected function setUp(): void
    {
        $this->contactRepository = new FixedContactRepository();
        $this->createContactUseCase = new CreateContactHandler($this->contactRepository);
    }

    public function testCreateContactWithMinimalData()
    {
        $contactDTO = new ContactMessage(
            'family_name_test',
            '810000001015'
        );
        ($this->createContactUseCase)($contactDTO);

        /** @var Contact $contact */
        $contact = $this->contactRepository->findById('1');

        $this->assertNotNull($contact);;
        $this->assertEquals('family_name_test', $contact->getFamilyName());
        $this->assertEquals('810000001015', $contact->getPpIdentifier());
    }
}