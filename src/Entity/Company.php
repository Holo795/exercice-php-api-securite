<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Model\Role;
use App\Repository\CompanyRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(
            security: 'is_granted("ROLE_USER", object)', // Access control for Get operation
        ),
        new Post(),
        new Put(
            security: 'is_granted("ROLE_ADMIN", object)', // Access control for Put operation
        ),
        new Delete(
            security: 'is_granted("ROLE_ADMIN", object)', // Access control for Delete operation
        ),
    ],
    normalizationContext: ['groups' => ['company:read']],    // Configures serialization for read operations
    denormalizationContext: ['groups' => ['company:write']]  // Configures serialization for write operations
)]
#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['company:read', 'project:read'])]
    private ?int $id = null; // Unique identifier for the Company entity

    #[ORM\Column(length: 14)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $siret = null; // SIRET number for the company

    #[ORM\Column(length: 255)]
    #[Groups(['company:read', 'company:write'])]
    private ?string $name = null; // Name of the company

    #[ORM\OneToOne(inversedBy: 'company', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(['company:read', 'company:write'])]
    #[ApiProperty(
        openapiContext: [
            'example' => '/api/addresses/1'
        ]
    )]
    private ?Address $address = null; // Associated Address entity with a one-to-one relation

    /**
     * @var Collection<int, Project>
     */
    #[ORM\OneToMany(targetEntity: Project::class, mappedBy: 'company', orphanRemoval: true)]
    #[Groups(['company:read'])]
    #[ApiProperty(
        openapiContext: [
            'example' => ['/api/projects/1','/api/projects/2']
        ]
    )]
    private Collection $project; // Projects related to the company

    /**
     * @var Collection<int, UserCompanyRole>
     */
    #[ORM\OneToMany(targetEntity: UserCompanyRole::class, mappedBy: 'company', orphanRemoval: true)]
    private Collection $userCompanyRoles; // User roles within the company

    // Method to check if a user is a member of the company
    public function isUserMember(User $user): bool
    {
        foreach ($this->userCompanyRoles as $userCompanyRole) {
            if ($userCompanyRole->getUser() === $user) {
                return true; // Returns true if user is found in userCompanyRoles
            }
        }
        return false;
    }

    public function __construct()
    {
        $this->userCompanyRoles = new ArrayCollection();
        $this->project = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getAddress(): ?Address
    {
        return $this->address;
    }

    public function setAddress(Address $address): static
    {
        $this->address = $address;

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProject(): Collection
    {
        return $this->project; // Returns collection of projects associated with the company
    }

    public function addProject(Project $project): static
    {
        if (!$this->project->contains($project)) {
            $this->project->add($project); // Adds project to collection if not already present
            $project->setCompany($this);   // Sets company reference in Project entity
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->project->removeElement($project)) {
            // Removes project reference if it points back to this company
            if ($project->getCompany() === $this) {
                $project->setCompany(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, UserCompanyRole>
     */
    public function getUserCompanyRoles(): Collection
    {
        return $this->userCompanyRoles; // Returns collection of user roles associated with the company
    }

    public function addUserCompanyRole(UserCompanyRole $userCompanyRole): static
    {
        if (!$this->userCompanyRoles->contains($userCompanyRole)) {
            $this->userCompanyRoles->add($userCompanyRole); // Adds user role to collection if not present
            $userCompanyRole->setCompany($this); // Sets company reference in UserCompanyRole entity
        }

        return $this;
    }

    public function removeUserCompanyRole(UserCompanyRole $userCompanyRole): static
    {
        if ($this->userCompanyRoles->removeElement($userCompanyRole)) {
            // Removes company reference in UserCompanyRole if it points back to this company
            if ($userCompanyRole->getCompany() === $this) {
                $userCompanyRole->setCompany(null);
            }
        }

        return $this;
    }

    public function getUserRole(User $user): ?Role
    {
        foreach ($this->userCompanyRoles as $userCompanyRole) {
            if ($userCompanyRole->getUser() === $user) {
                return $userCompanyRole->getRole(); // Returns role of the user within this company
            }
        }
        return Role::NONE; // Returns 'NONE' role if user is not found in userCompanyRoles
    }
}
