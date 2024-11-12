<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Model\Role;
use App\Repository\UserCompanyRoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(
            security: 'is_granted("ROLE_ADMIN", object)' // Restricts GET access to ROLE_ADMIN
        ),
        new Post(
            securityPostDenormalize: 'is_granted("ROLE_ADMIN", object)' // Restricts POST access to ROLE_ADMIN
        ),
        new Put(
            security: 'is_granted("ROLE_ADMIN", object)' // Restricts PUT access to ROLE_ADMIN
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN", object)' // Restricts DELETE access to ROLE_ADMIN
        ),
    ],
    normalizationContext: ['groups' => ['userCompanyRole:read']], // Configures groups for read operations
    denormalizationContext: ['groups' => ['userCompanyRole:write']] // Configures groups for write operations
)]
#[ORM\Entity(repositoryClass: UserCompanyRoleRepository::class)]
class UserCompanyRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['userCompanyRole:read'])]
    private ?int $id = null; // Unique identifier for each UserCompanyRole instance

    #[ORM\ManyToOne(inversedBy: 'userCompanyRoles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['userCompanyRole:read', 'userCompanyRole:write'])]
    #[ApiProperty(
        openapiContext: [
            'example' => '/api/users/1'
        ]
    )]
    private ?User $user = null; // Reference to the associated User

    #[ORM\ManyToOne(inversedBy: 'userCompanyRoles')]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['userCompanyRole:read', 'userCompanyRole:write'])]
    #[ApiProperty(
        openapiContext: [
            'example' => '/api/companies/1'
        ]
    )]
    private ?Company $company = null; // Reference to the associated Company

    #[ORM\Column(type: 'string', enumType: Role::class)]
    #[Groups(['userCompanyRole:read', 'userCompanyRole:write'])]
    #[ApiProperty(
        openapiContext: [
            'example' => 'ROLE_EXAMPLE'
        ]
    )]
    private ?Role $role = Role::USER; // Enum-based role (e.g., ADMIN, MANAGER)

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user; // Retrieves the associated User
    }

    public function setUser(?User $user): static
    {
        $this->user = $user; // Sets the associated User

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company; // Retrieves the associated Company
    }

    public function setCompany(?Company $company): static
    {
        $this->company = $company; // Sets the associated Company

        return $this;
    }

    public function getRole(): ?Role
    {
        return $this->role; // Retrieves the role for this User in the Company
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role; // Sets the role for this User in the Company

        return $this;
    }
}
