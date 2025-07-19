<?php

declare(strict_types=1);

namespace App\Application\MessageHandler;

use App\Application\Message\Organization\CreateOrganizationMessage;
use App\Application\Model\HandlerResult;
use App\Application\Port\IOrganizationRepository;
use App\Entity\Address;
use App\Entity\Organization;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class CreateOrganizationHandler
{
    public function __construct(private IOrganizationRepository $organizationRepository)
    {
    }

    public function __invoke(CreateOrganizationMessage $createOrganizationMessage): HandlerResult
    {
        $address = new Address();
        $address
            ->setStreet($createOrganizationMessage->street)
            ->setStreet2($createOrganizationMessage->street2)
            ->setManualZipCode($createOrganizationMessage->manualZipCode)
            ->setManualCity($createOrganizationMessage->manualCity)
        ;

        $organization = new Organization();
        $organization
            ->setTechnicalId($createOrganizationMessage->technicalId)
            ->setName($createOrganizationMessage->name)
            ->setAddress($address)
            ->setEmailAddress($createOrganizationMessage->emailAddress)
            ->setPhoneNumber($createOrganizationMessage->phoneNumber)
            ->setPrivateFromString($createOrganizationMessage->private)
        ;

        try {
            $this->organizationRepository->save($organization);
        } catch (\Exception $e) {
            return HandlerResult::failure($e->getMessage());
        }

        return HandlerResult::success();
    }
}
