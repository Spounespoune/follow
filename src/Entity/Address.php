<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\Trait\Hashable;
use App\Entity\Trait\SoftDeletable;
use App\Infrastructure\ForProduction\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address implements HashableInterface
{
    use Hashable;
    use SoftDeletable;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(name: 'street', type: 'string', length: 255, nullable: true)]
    private ?string $street = null;

    #[ORM\Column(name: 'street2', type: 'string', length: 255, nullable: true)]
    private ?string $street2 = null;

    #[ORM\Column(name: 'manual_zip_code', type: 'string', nullable: true)]
    private ?string $manualZipCode = null;

    #[ORM\Column(name: 'manual_city', type: 'string', nullable: true)]
    private ?string $manualCity = null;

    #[ORM\Column(name: 'city', type: 'string', nullable: true)]
    private ?string $city = null;

    #[ORM\Column(name: 'zip_code', type: 'string', nullable: true)]
    private ?string $zipCode = null;

    #[ORM\Column(name: 'country', type: 'string', nullable: true)]
    private ?string $country = null;

    public static function create(
        ?string $street = null,
        ?string $street2 = null,
        ?string $manualZipCode = null,
        ?string $manualCity = null,
    ): Address {
        return new self()
            ->setStreet($street)
            ->setStreet2($street2)
            ->setManualZipCode($manualZipCode)
            ->setManualCity($manualCity)
        ;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Address
    {
        $this->id = $id;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(?string $street): Address
    {
        $this->street = $street;

        return $this;
    }

    public function getStreet2(): ?string
    {
        return $this->street2;
    }

    public function setStreet2(?string $street2): Address
    {
        $this->street2 = $street2;

        return $this;
    }

    public function getManualZipCode(): ?string
    {
        return $this->manualZipCode;
    }

    public function setManualZipCode(?string $manualZipCode): Address
    {
        $this->manualZipCode = $manualZipCode;

        return $this;
    }

    public function getManualCity(): ?string
    {
        return $this->manualCity;
    }

    public function setManualCity(?string $manualCity): Address
    {
        $this->manualCity = $manualCity;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): Address
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(?string $country): Address
    {
        $this->country = $country;

        return $this;
    }

    public function getCityName(): ?string
    {
        return $this->city ?? $this->manualCity;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode ?? $this->manualZipCode;
    }
}
