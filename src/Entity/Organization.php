<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\Hashable;
use App\Entity\Trait\SoftDeletable;
use App\Entity\Trait\Timestampable;
use App\Infrastructure\ForProduction\Repository\OrganizationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
class Organization implements HashableInterface
{
    use Hashable;
    use Timestampable;
    use SoftDeletable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'technical_id', type: 'string', length: 60, nullable: false)]
    private string $technicalId;

    #[ORM\Column(name: 'name', type: 'string', nullable: true)]
    private ?string $name;

    #[ORM\OneToOne(targetEntity: Address::class, cascade: ['persist'])]
    #[ORM\JoinColumn(name: 'address_id', referencedColumnName: 'id')]
    #[Assert\Valid]
    private Address $address;

    #[ORM\Column(name: 'email_address', type: 'string', nullable: true)]
    #[Assert\Email(mode: 'strict')]
    private ?string $emailAddress;

    #[ORM\Column(name: 'phone_number', type: 'string', length: 15, nullable: true)]
    #[Assert\Regex(pattern: "/^$|^\w{9,15}$/")]
    private ?string $phoneNumber;

    #[ORM\Column(name: 'private', type: 'boolean', options: ['default' => 0])]
    private bool $private = false;

    public static function create(
        string $technicalId,
        ?string $name = null,
        ?Address $address = null,
        ?string $emailAddress = null,
        ?string $phoneNumber = null,
        bool $private = false,
    ): Organization {
        return new self()
            ->setTechnicalId($technicalId)
            ->setName($name)
            ->setAddress($address)
            ->setEmailAddress($emailAddress)
            ->setPhoneNumber($phoneNumber)
            ->setPrivate($private)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTechnicalId(): string
    {
        return $this->technicalId;
    }

    public function setTechnicalId($technicalId): static
    {
        $this->technicalId = $technicalId;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName($name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getEmailAddress(): ?string
    {
        return $this->emailAddress;
    }

    public function setEmailAddress($emailAddress): static
    {
        $this->emailAddress = $emailAddress;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber($phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function isPrivate(): bool
    {
        return $this->private;
    }

    public function setPrivate(bool $private): static
    {
        $this->private = $private;

        return $this;
    }

    public function setPrivateFromString(string $private): static
    {
        $this->private = $this->determinePrivacyFromString($private);

        return $this;
    }

    private function determinePrivacyFromString(string $activitySectorString): bool
    {
        return !str_contains(strtolower($activitySectorString), 'public');
    }
}
