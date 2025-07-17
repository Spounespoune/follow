<?php

declare(strict_types=1);

namespace App\Tests\Units\MessageHandler;

use App\Application\Message\Contact\DeleteContactMessage;
use App\Application\MessageHandler\DeleteContactHandler;
use App\Entity\Contact;
use App\Infrastructure\ForTest\Repository\FixedContactRepository;
use PHPUnit\Framework\TestCase;

class DeleteContactHandlerTest extends TestCase
{
    private DeleteContactHandler $deleteContactHandler;
    private FixedContactRepository $contactRepository;

    protected function setUp(): void
    {
        $contact = Contact::create(
            '10000001015',
            'family_name_test',
        );
        $contact->updatedAt = new \DateTimeImmutable('2022-01-01');

        $this->contactRepository = new FixedContactRepository([$contact]);
        $this->deleteContactHandler = new DeleteContactHandler($this->contactRepository);
    }

    public function testDeleteContactWhenUpdatedSinceOneWeekAgo()
    {
        $deleteMessage = new DeleteContactMessage(executionDate: new \DateTimeImmutable('2022-01-08'));
        ($this->deleteContactHandler)($deleteMessage);

        /** @var Contact $contact */
        $contact = $this->contactRepository->findByPpIdentifier('10000001015');

        $this->assertNotNull($contact->deletedAt);
    }

    public function testNoDeleteContactWhenUpdatedLessThanOneWeekAgo()
    {
        $deleteMessage = new DeleteContactMessage(executionDate: new \DateTimeImmutable('2022-01-05'));
        ($this->deleteContactHandler)($deleteMessage);

        /** @var Contact $contact */
        $contact = $this->contactRepository->findByPpIdentifier('10000001015');

        $this->assertNull($contact->deletedAt);
    }

    public function testDeleteOneContact()
    {
        $deleteMessage = new DeleteContactMessage(executionDate: new \DateTimeImmutable('2022-01-08'));
        $countContacteDeleted = ($this->deleteContactHandler)($deleteMessage);

        $this->assertEquals(1, $countContacteDeleted);
    }
}
