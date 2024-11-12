<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\ProjectRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(
            security: 'is_granted("ROLE_CONSULTANT", object)' // Only users with ROLE_CONSULTANT and higher can view a project
        ),
        new Post(
            securityPostDenormalize: 'is_granted("ROLE_MANAGER", object)' // Only users with ROLE_MANAGER and higher can view a project
        ),
        new Put(
            security: 'is_granted("ROLE_MANAGER", object)' // Only users with ROLE_MANAGER and higher can update a project
        ),
        new Delete(
            security: 'is_granted("ROLE_MANAGER", object)' // Only users with ROLE_MANAGER and higher can delete a project
        ),
    ],
    normalizationContext: ['groups' => ['project:read']],    // Defines groups for serialization (reading)
    denormalizationContext: ['groups' => ['project:write']]  // Defines groups for serialization (writing)
)]
#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['project:read', 'company:read'])]
    private ?int $id = null; // Unique identifier for the Project entity

    #[ORM\Column(length: 255)]
    #[Groups(['project:read', 'project:write'])]
    private ?string $title = null; // Title of the project

    #[ORM\Column(length: 255)]
    #[Groups(['project:read', 'project:write'])]
    private ?string $description = null; // Brief description of the project

    #[ORM\Column]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['project:read', 'project:write'])]
    private ?\DateTimeImmutable $createdAt = null; // Timestamp for project creation date

    #[ORM\ManyToOne(inversedBy: 'project')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['project:read', 'project:write'])]
    #[ApiProperty(
        openapiContext: [
            'example' => '/api/companies/1'
        ]
    )]
    private ?Company $company = null; // Company associated with the project

    public function __construct()
    {
        // Automatically sets createdAt to the current date and time if not already set
        if ($this->createdAt === null) {
            $this->createdAt = new \DateTimeImmutable();
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt; // Returns the project creation timestamp
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt; // Manually sets the project creation timestamp

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company; // Returns the associated Company entity
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company; // Sets the associated Company entity

        return $this;
    }
}
