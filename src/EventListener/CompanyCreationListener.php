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

    public function postPersist(Company $company, PostPersistEventArgs $event): void
    {
        $user = $this->security->getUser();

        if (!$user) {
            return;
        }

        $userCompanyRole = new UserCompanyRole();
        $userCompanyRole->setCompany($company);
        $userCompanyRole->setUser($user);
        $userCompanyRole->setRole(Role::ADMIN);

        $this->entityManager->persist($userCompanyRole);
        $this->entityManager->flush();
    }
}
