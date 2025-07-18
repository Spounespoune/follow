<?php

declare(strict_types=1);

namespace App\Application\MessageHandler;

use App\Application\Message\Organization\UpdateOrganizationMessage;
use App\Application\Port\IOrganizationRepository;
use App\Entity\Address;
use App\Entity\Organization;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
readonly class UpdateOrganizationHandler
{
    public function __construct(private IOrganizationRepository $organizationRepository)
    {
    }

    public function __invoke(UpdateOrganizationMessage $updateOrganizationMessage): void
    {
        $organizationPersist = $this->organizationRepository->findByTechnicalId($updateOrganizationMessage->technicalId);

        if (null === $organizationPersist) {
            throw new \Exception('Organization not found');
        }

        $address = Address::create(
            $updateOrganizationMessage->street,
            $updateOrganizationMessage->street2,
            $updateOrganizationMessage->manualZipCode,
            $updateOrganizationMessage->manualCity,
        );

        $organization = Organization::create(
            $updateOrganizationMessage->technicalId,
            $updateOrganizationMessage->name,
            $address,
            $updateOrganizationMessage->emailAddress,
            $updateOrganizationMessage->phoneNumber,
        );

        $organization->setPrivateFromString($updateOrganizationMessage->private);

        if ($organizationPersist->identicalTo($organization)) {
            return;
        }

        $needUpdateAddress = $this->updateAdresseFields($organizationPersist->getAddress(), $address);

        if ($needUpdateAddress) {
            $organization->setAddress($address);
        }

        $this->updateOrganizationFields($organizationPersist, $organization);

        $this->organizationRepository->save($organization);
    }

    private function updateAdresseFields(Address $persistAddress, Address $address): bool
    {
        $needUpdate = false;

        if (null !== $address->getStreet() && $persistAddress->getStreet() !== $address->getStreet()) {
            $persistAddress->setStreet($address->getStreet());
            $needUpdate = true;
        }

        if (null !== $address->getStreet2() && $persistAddress->getStreet2() !== $address->getStreet2()) {
            $persistAddress->setStreet2($address->getStreet2());
            $needUpdate = true;
        }

        if (null !== $address->getManualZipCode() && $persistAddress->getManualZipCode() !== $address->getManualZipCode()) {
            $persistAddress->setManualZipCode($address->getManualZipCode());
            $needUpdate = true;
        }

        if (null !== $address->getManualCity() && $persistAddress->getManualCity() !== $address->getManualCity()) {
            $persistAddress->setManualCity($address->getManualCity());
            $needUpdate = true;
        }

        if (null !== $persistAddress->deletedAt) {
            $persistAddress->deletedAt = null;
        }

        return $needUpdate;
    }

    private function updateOrganizationFields(Organization $persistOrganization, Organization $organization): void
    {
        if (null !== $organization->getName() && $persistOrganization->getName() !== $organization->getName()) {
            $persistOrganization->setName($organization->getName());
        }

        if (null !== $organization->getEmailAddress() && $persistOrganization->getEmailAddress() !== $organization->getEmailAddress()) {
            $persistOrganization->setEmailAddress($organization->getEmailAddress());
        }

        if (null !== $organization->getPhoneNumber() && $persistOrganization->getPhoneNumber() !== $organization->getPhoneNumber()) {
            $persistOrganization->setPhoneNumber($organization->getPhoneNumber());
        }

        if (null !== $organization->isPrivate() && $persistOrganization->isPrivate() !== $organization->isPrivate()) {
            $persistOrganization->setPrivate($organization->isPrivate());
        }

        if (null !== $persistOrganization->deletedAt) {
            $persistOrganization->deletedAt = null;
        }
    }
}
