<?php

declare(strict_types=1);

namespace App\Tests\Units\MessageHandler;

use App\Application\Message\Organization\CreateOrganizationMessage;
use App\Application\MessageHandler\CreateOrganizationHandler;
use App\Infrastructure\ForTest\Repository\FixedOrganizationRepository;
use PHPUnit\Framework\TestCase;

class CreateOrganizationHandlerTest extends TestCase
{
    private CreateOrganizationHandler $createOrganizationHandler;
    private FixedOrganizationRepository $fixedOrganizationRepository;

    protected function setUp(): void
    {
        $this->fixedOrganizationRepository = new FixedOrganizationRepository();
        $this->createOrganizationHandler = new CreateOrganizationHandler($this->fixedOrganizationRepository);
    }

    public function testCreateOrganisation()
    {
        $createOrganizationMessage = new CreateOrganizationMessage(
            'F010000024',
            'organisation_name',
        );
        ($this->createOrganizationHandler)($createOrganizationMessage);

        $organization = $this->fixedOrganizationRepository->findByTechnicalId('F010000024');

        $this->assertNotNull($organization);
    }

    public function testCreateOrganisationPublic()
    {
        $createOrganizationMessage = new CreateOrganizationMessage(
            'F010000024',
            'organisation_name',
            private: 'Etablissement Public de santé'
        );
        ($this->createOrganizationHandler)($createOrganizationMessage);

        $organization = $this->fixedOrganizationRepository->findByTechnicalId('F010000024');

        $this->assertNotNull($organization);
        $this->assertFalse($organization->isPrivate());
    }
}
