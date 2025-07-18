<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\Hashable;
use App\Entity\Trait\SoftDeletable;
use App\Entity\Trait\Timestampable;
use App\Infrastructure\ForProduction\Repository\ContactRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact implements HashableInterface
{
    use Hashable;
    use SoftDeletable;
    use Timestampable;

    public const int PP_IDENTIFIER_TYPE_ADELI = 0;
    public const int PP_IDENTIFIER_TYPE_RPPS = 8;
    public const array PP_IDENTIFIER_TYPES = [self::PP_IDENTIFIER_TYPE_RPPS, self::PP_IDENTIFIER_TYPE_ADELI];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'pp_identifier', type: 'string', length: 11, nullable: false)]
    #[Assert\Length(min: '8', max: '11')]
    private string $ppIdentifier;

    #[ORM\Column(name: 'pp_identifier_type', type: 'smallint', nullable: true)]
    #[Assert\Choice(choices: Contact::PP_IDENTIFIER_TYPES)]
    private ?int $ppIdentifierType;

    #[ORM\Column(name: 'title', type: 'string', nullable: true)]
    private ?string $title;

    #[ORM\Column(name: 'first_name', type: 'string', nullable: true)]
    private ?string $firstName;

    #[ORM\Column(name: 'family_name', type: 'string', nullable: false)]
    #[Assert\NotBlank]
    private ?string $familyName;

    #[ORM\ManyToMany(targetEntity: Organization::class, cascade: ['persist'])]
    #[ORM\JoinTable(name: 'contact_organizations')]
    #[ORM\JoinColumn(name: 'contact_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'organization_id', referencedColumnName: 'id')]
    #[Assert\Valid]
    private $organizations;

    public function __construct()
    {
        $this->organizations = new ArrayCollection();
    }

    public static function create(
        string $ppIdentifier,
        string $familyName,
        ?int $ppIdentifierType = null,
        ?string $firstName = null,
        ?string $title = null,
    ): Contact {
        return new self()
            ->setPpIdentifier($ppIdentifier)
            ->setPpIdentifierType($ppIdentifierType)
            ->setTitle($title)
            ->setFirstName($firstName)
            ->setFamilyName($familyName)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Contact
    {
        $this->id = $id;

        return $this;
    }

    public function getPpIdentifier(): ?string
    {
        return $this->ppIdentifier;
    }

    public function setPpIdentifier($ppIdentifier): static
    {
        $this->ppIdentifier = $ppIdentifier;

        return $this;
    }

    public function getPpIdentifierType(): ?int
    {
        return $this->ppIdentifierType;
    }

    public function setPpIdentifierType(?int $ppIdentifierType): static
    {
        $this->ppIdentifierType = $ppIdentifierType;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle($title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName($firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getFamilyName(): ?string
    {
        return $this->familyName;
    }

    public function setFamilyName($familyName): static
    {
        $this->familyName = $familyName;

        return $this;
    }

    public function getOrganizations(): ArrayCollection
    {
        return $this->organizations;
    }

    public function setOrganizations(ArrayCollection $organizations): Contact
    {
        $this->organizations = $organizations;

        return $this;
    }

    public function addOrganization(Organization $organization): void
    {
        $this->organizations->add($organization);
    }

    public function removeOrganization(Organization $organization): void
    {
        $this->organizations->removeElement($organization);
    }

    public function hasOrganization(Organization $organization): bool
    {
        return $this->organizations->contains($organization);
    }

    public function hasOrganizationWithTechnicalId(string $technicalId): bool
    {
        if (array_any((array) $this->organizations, fn ($organization) => $organization->getTechnicalId() === $technicalId)) {
            return true;
        }

        return false;
    }
}
