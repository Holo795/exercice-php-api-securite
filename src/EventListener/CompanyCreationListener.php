<?php

// src/EventListener/UserChangedNotifier.php
namespace App\EventListener;

use App\Entity\Company;
use App\Entity\UserCompanyRole;
use App\Model\Role;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;

// Listen for the postPersist event on the Company entity
// When a Company entity is persisted, this listener will create a new UserCompanyRole entity
// and assign it to the currently authenticated user with the role ADMIN
#[AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: Company::class)]
class CompanyCreationListener
{
    private Security $security;
    private EntityManagerInterface $entityManager;

    public function __construct(Security $security, EntityManagerInterface $entityManager)
    {
        $this->security = $security;
        $this->entityManager = $entityManager;
    }

    // Called after a Company entity is persisted
    public function postPersist(Company $company, PostPersistEventArgs $event): void
    {
        // Get the currently authenticated user
        $user = $this->security->getUser();

        // Exit if there is no authenticated user
        if (!$user) {
            return;
        }

        // Create a new UserCompanyRole, assign it to the user, company, and set the role to ADMIN
        $userCompanyRole = new UserCompanyRole();
        $userCompanyRole->setCompany($company);
        $userCompanyRole->setUser($user);
        $userCompanyRole->setRole(Role::ADMIN);

        // Persist and save the new UserCompanyRole entity
        $this->entityManager->persist($userCompanyRole);
        $this->entityManager->flush();
    }
}
