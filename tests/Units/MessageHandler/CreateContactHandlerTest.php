<?php

namespace App\Tests\Units\MessageHandler;

use App\Application\Message\Contact\CreateContactMessage;
use App\Application\MessageHandler\CreateContactHandler;
use App\Entity\Contact;
use App\Infrastructure\ForTest\Repository\FixedContactRepository;
use PHPUnit\Framework\TestCase;

class CreateContactHandlerTest extends TestCase
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
        $createContactMessage = new CreateContactMessage(
            '10000001015',
            'family_name_test',
            ppIdentifierType: Contact::PP_IDENTIFIER_TYPE_RPPS
        );
        $result = ($this->createContactUseCase)($createContactMessage);

        $this->assertTrue($result->success);

        /** @var Contact $contact */
        $contact = $this->contactRepository->findByPpIdentifier('10000001015');

        $this->assertNotNull($contact);
        $this->assertEquals('family_name_test', $contact->getFamilyName());
    }
}