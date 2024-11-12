<?php

namespace App\DataFixtures;

use App\Entity\Address;
use App\Entity\Company;
use App\Entity\Project;
use App\Entity\User;
use App\Entity\UserCompanyRole;
use App\Model\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $user = new User();
        $user->setEmail('user@local.host')
            ->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $manager->persist($user);

        $admin = new User();
        $admin->setEmail('admin@local.host')
            ->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $manager->persist($admin);

        $userManager = new User();
        $userManager->setEmail('manager@local.host')
            ->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $manager->persist($userManager);

        $userConsultant = new User();
        $userConsultant->setEmail('consultant@local.host')
            ->setPassword(password_hash('password', PASSWORD_BCRYPT));
        $manager->persist($userConsultant);


        $address1 = new Address();
        $address1->setNumber('1')
            ->setStreet('Rue Fulton')
            ->setPostalCode('75013')
            ->setCity('Paris')
            ->setCountry('France');
        $manager->persist($address1);

        $address2 = new Address();
        $address2->setNumber('2')
            ->setStreet('Rue Fulton')
            ->setPostalCode('75013')
            ->setCity('Paris')
            ->setCountry('France');
        $manager->persist($address2);

        $company1 = new Company();
        $company1->setName('Company test')
            ->setAddress($address1)
            ->setSiret("12345678901234");
        $manager->persist($company1);

        $company2 = new Company();
        $company2->setName('Company test 2')
            ->setAddress($address2)
            ->setSiret("12345678901235");
        $manager->persist($company2);

        $userCompanyRole = new UserCompanyRole();
        $userCompanyRole->setUser($user)
            ->setCompany($company1)
            ->setRole(Role::USER);
        $manager->persist($userCompanyRole);

        $adminCompanyRole = new UserCompanyRole();
        $adminCompanyRole->setUser($admin)
            ->setCompany($company1)
            ->setRole(Role::ADMIN);
        $manager->persist($adminCompanyRole);

        $managerCompanyRole = new UserCompanyRole();
        $managerCompanyRole->setUser($userManager)
            ->setCompany($company1)
            ->setRole(Role::MANAGER);
        $manager->persist($managerCompanyRole);

        $consultantCompanyRole = new UserCompanyRole();
        $consultantCompanyRole->setUser($userConsultant)
            ->setCompany($company1)
            ->setRole(Role::CONSULTANT);
        $manager->persist($consultantCompanyRole);

        $project = new Project();
        $project->setTitle('php-api-securite')
            ->setDescription('Exercice PHP API Sécurité')
            ->setCompany($company1);
        $manager->persist($project);

        $manager->flush();
    }
}
