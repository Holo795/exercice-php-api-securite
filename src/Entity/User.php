<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(
            normalizationContext: ['groups' => ['user:read']]
        ),
        new Post(
            denormalizationContext: ['groups' => ['user:write']]
        ),
        new Put(
            denormalizationContext: ['groups' => ['user:write']]
        ),
        new Delete()
    ],
)]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null; // Unique identifier for each user

    #[ORM\Column(length: 180)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user:read', 'user:write'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Groups(['user:write'])]
    private ?string $password = null; // Encrypted password

    /**
     * @var Collection<int, UserCompanyRole>
     */
    #[ORM\OneToMany(targetEntity: UserCompanyRole::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $userCompanyRoles; // Collection of roles this user has within different companies

    public function __construct()
    {
        $this->userCompanyRoles = new ArrayCollection(); // Initializes an empty collection for company roles
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string)$this->email; // Returns the email as a unique identifier
    }

    /**
     * @return list<string>
     * @see UserInterface
     *
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER'; // Ensures each user has at least ROLE_USER
        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles; // Sets user roles

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password; // Returns the hashed password
    }

    public function setPassword(string $password): static
    {
        $this->password = $password; // Sets the hashed password

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // Method to clear any sensitive data (e.g., plain password) from memory
    }

    /**
     * @return Collection<int, UserCompanyRole>
     */
    public function getUserCompanyRoles(): Collection
    {
        return $this->userCompanyRoles; // Returns the user's roles in various companies
    }

    public function addUserCompanyRole(UserCompanyRole $userCompanyRole): static
    {
        if (!$this->userCompanyRoles->contains($userCompanyRole)) {
            $this->userCompanyRoles->add($userCompanyRole);
            $userCompanyRole->setUser($this); // Links the UserCompanyRole to this user
        }

        return $this;
    }

    public function removeUserCompanyRole(UserCompanyRole $userCompanyRole): static
    {
        if ($this->userCompanyRoles->removeElement($userCompanyRole)) {
            if ($userCompanyRole->getUser() === $this) {
                $userCompanyRole->setUser(null); // Unlinks the UserCompanyRole from this user
            }
        }

        return $this;
    }
}
