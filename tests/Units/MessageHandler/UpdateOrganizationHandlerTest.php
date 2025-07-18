<?php

declare(strict_types=1);

namespace App\Tests\Units\MessageHandler;

use App\Application\Message\Organization\UpdateOrganizationMessage;
use App\Application\MessageHandler\UpdateOrganizationHandler;
use App\Entity\Address;
use App\Entity\Organization;
use App\Infrastructure\ForTest\Repository\FixedOrganizationRepository;
use PHPUnit\Framework\TestCase;

class UpdateOrganizationHandlerTest extends TestCase
{
    private UpdateOrganizationHandler $updateOrganizationHandler;
    private FixedOrganizationRepository $organizationRepository;

    protected function setUp(): void
    {
        $address = Address::create(
            'Rue de la Paix',
            'Bâtiment A',
            '75001',
            'Paris'
        );

        $organization = Organization::create(
            'ORG001',
            'Test Organization',
            $address,
            'test@example.com',
            '0123456789',
            false
        );

        $this->organizationRepository = new FixedOrganizationRepository([$organization]);
        $this->updateOrganizationHandler = new UpdateOrganizationHandler($this->organizationRepository);
    }

    public function testUpdateOrganization()
    {
        $updateOrganizationMessage = new UpdateOrganizationMessage(
            'ORG001',
            'Updated Organization Name',
            'updated@example.com',
            '0987654321',
            'public',
            'Rue de Rivoli',
            'Étage 2',
            '75002',
            'Paris'
        );

        ($this->updateOrganizationHandler)($updateOrganizationMessage);

        /** @var Organization $organization */
        $organization = $this->organizationRepository->findByTechnicalId('ORG001');

        $this->assertNotNull($organization);
        $this->assertEquals('Updated Organization Name', $organization->getName());
        $this->assertEquals('updated@example.com', $organization->getEmailAddress());
        $this->assertEquals('0987654321', $organization->getPhoneNumber());
        $this->assertFalse($organization->isPrivate());
        $this->assertEquals('Rue de Rivoli', $organization->getAddress()->getStreet());
        $this->assertEquals('Étage 2', $organization->getAddress()->getStreet2());
        $this->assertEquals('75002', $organization->getAddress()->getManualZipCode());
        $this->assertEquals('Paris', $organization->getAddress()->getManualCity());
    }

    public function testUpdateOrganizationWithMinimalData()
    {
        $updateOrganizationMessage = new UpdateOrganizationMessage(
            'ORG001',
            'Minimal Update'
        );

        ($this->updateOrganizationHandler)($updateOrganizationMessage);

        /** @var Organization $organization */
        $organization = $this->organizationRepository->findByTechnicalId('ORG001');

        $this->assertNotNull($organization);
        $this->assertEquals('Minimal Update', $organization->getName());
        $this->assertEquals('test@example.com', $organization->getEmailAddress()); // Should remain unchanged
        $this->assertEquals('0123456789', $organization->getPhoneNumber()); // Should remain unchanged
        $this->assertTrue($organization->isPrivate()); // Should remain unchanged
    }

    public function testUpdateOrganizationThrowsExceptionWhenNotFound()
    {
        $updateOrganizationMessage = new UpdateOrganizationMessage(
            'NONEXISTENT',
            'Should not work'
        );

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Organization not found');

        ($this->updateOrganizationHandler)($updateOrganizationMessage);
    }

    public function testUpdateOrganizationDoesNothingWhenDataIsIdentical()
    {
        // Create an organization with the same data as the one in setUp
        $updateOrganizationMessage = new UpdateOrganizationMessage(
            'ORG001',
            'Test Organization',
            'test@example.com',
            '0123456789',
            'public',
            'Rue de la Paix',
            'Bâtiment A',
            '75001',
            'Paris'
        );

        ($this->updateOrganizationHandler)($updateOrganizationMessage);

        /** @var Organization $organization */
        $organization = $this->organizationRepository->findByTechnicalId('ORG001');

        $this->assertNotNull($organization);
        $this->assertEquals('Test Organization', $organization->getName());
        $this->assertEquals('test@example.com', $organization->getEmailAddress());
        $this->assertEquals('0123456789', $organization->getPhoneNumber());
        $this->assertFalse($organization->isPrivate());
    }
}