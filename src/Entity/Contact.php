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

    public const PP_IDENTIFIER_TYPE_ADELI = 0;
    public const PP_IDENTIFIER_TYPE_RPPS = 8;
    public const PP_IDENTIFIER_TYPES = [self::PP_IDENTIFIER_TYPE_RPPS, self::PP_IDENTIFIER_TYPE_ADELI];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'pp_identifier', type: 'string', length: 11, nullable: false)]
    #[Assert\Length(min: '8', max: '11')]
    private $ppIdentifier;

    #[ORM\Column(name: 'pp_identifier_type', type: 'smallint', nullable: true)]
    #[Assert\Choice(choices: Contact::PP_IDENTIFIER_TYPES)]
    private $ppIdentifierType;

    #[ORM\Column(name: 'title', type: 'string', nullable: true)]
    private $title;

    #[ORM\Column(name: 'first_name', type: 'string', nullable: true)]
    private $firstName;

    #[ORM\Column(name: 'family_name', type: 'string', nullable: false)]
    #[Assert\NotBlank]
    private $familyName;

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

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Contact
    {
        $this->id = $id;

        return $this;
    }

    public function getPpIdentifier()
    {
        return $this->ppIdentifier;
    }

    /**
     * @return Contact
     */
    public function setPpIdentifier($ppIdentifier)
    {
        $this->ppIdentifier = $ppIdentifier;

        return $this;
    }

    public function getPpIdentifierType()
    {
        return $this->ppIdentifierType;
    }

    /**
     * @return Contact
     */
    public function setPpIdentifierType($ppIdentifierType)
    {
        $this->ppIdentifierType = $ppIdentifierType;

        return $this;
    }

    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return Contact
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @return Contact
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getFamilyName()
    {
        return $this->familyName;
    }

    /**
     * @return Contact
     */
    public function setFamilyName($familyName)
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
}
