<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use App\Repository\AddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(),  // Allows retrieving an Address resource
        new Post(), // Allows creating a new Address resource
    ],
    normalizationContext: ['groups' => ['address:read']],   // Configures which groups are included in serialized output
    denormalizationContext: ['groups' => ['address:write']], // Configures groups allowed for deserialization (input data)
)]
#[ORM\Entity(repositoryClass: AddressRepository::class)]
class Address
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['address:read', 'company:read'])] // Adds 'id' to readable output for address and company resources
    private ?int $id = null;

    #[ORM\Column(length: 10)]
    #[Groups(['address:read', 'address:write', 'company:read'])]
    private ?string $number = null;

    #[ORM\Column(length: 255)]
    #[Groups(['address:read', 'address:write', 'company:read'])]
    private ?string $street = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['address:read', 'address:write', 'company:read'])]
    private ?string $additional_info = null; // Optional field for additional address info

    #[ORM\Column(length: 10)]
    #[Groups(['address:read', 'address:write', 'company:read'])]
    private ?string $postal_code = null;

    #[ORM\Column(length: 100)]
    #[Groups(['address:read', 'address:write', 'company:read'])]
    private ?string $city = null;

    #[ORM\Column(length: 100)]
    #[Groups(['address:read', 'address:write', 'company:read'])]
    private ?string $country = null;

    #[ORM\OneToOne(mappedBy: 'address', cascade: ['persist', 'remove'])]
    private ?Company $company = null; // One-to-one relationship with the Company entity

    // Getter and setter methods for each property
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): static
    {
        $this->number = $number;

        return $this;
    }

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getAdditionalInfo(): ?string
    {
        return $this->additional_info;
    }

    public function setAdditionalInfo(?string $additional_info): static
    {
        $this->additional_info = $additional_info;

        return $this;
    }

    public function getPostalCode(): ?string
    {
        return $this->postal_code;
    }

    public function setPostalCode(string $postal_code): static
    {
        $this->postal_code = $postal_code;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function setCountry(string $country): static
    {
        $this->country = $country;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(Company $company): static
    {
        // Ensures the address is correctly set on the Company entity
        if ($company->getAddress() !== $this) {
            $company->setAddress($this);
        }

        $this->company = $company;

        return $this;
    }
}
