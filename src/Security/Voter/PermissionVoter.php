<?php

namespace App\Security\Voter;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\Project;
use App\Entity\User;
use App\Entity\UserCompanyRole;
use App\Model\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class PermissionVoter extends Voter
{
    /**
     * Determines if the attribute is supported by this voter.
     * Only allows attributes that are valid roles defined in the Role model.
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return in_array($attribute, Role::getRolesArray());
    }

    /**
     * Grants or denies access based on the user's role relative to the required role.
     * Compares the user's role on the subject to the required role (attribute).
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        // Deny access if the user is not authenticated.
        if (!$user instanceof UserInterface) {
            return false;
        }

        $userRole = $this->getUserRole($user, $subject);

        if ($userRole == Role::NONE) {
            return false;
        }

        // Check if the user's role meets or exceeds the required role for access.
        return $userRole->getRoleValue() >= Role::roleFromStrValue($attribute)->getRoleValue();
    }

    /**
     * Determines the role of the user on a given subject.
     * - If the subject is a Project, Address, or UserCompanyRole, get the role in the associated Company.
     * - If the subject is a Company, get the role directly from the company.
     * - Otherwise, return the default role (USER).
     */
    public function getUserRole(User $user, mixed $subject): Role
    {
        if ($subject instanceof Project || $subject instanceof Address || $subject instanceof UserCompanyRole) {
            return $subject->getCompany()->getUserRole($user);  // Get role from associated Company
        } elseif ($subject instanceof Company) {
            return $subject->getUserRole($user);  // Get role directly from Company
        } else {
            return Role::USER;  // Default role if no specific association
        }
    }
}
