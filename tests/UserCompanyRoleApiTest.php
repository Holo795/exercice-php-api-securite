<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Model\Role;

class UserCompanyRoleApiTest extends ApiTestCase
{
    private string $token; // JWT token for authentication

    protected function setUp(): void
    {
        parent::setUp();
        $this->token = $this->getToken();
    }

    private function getToken(): string
    {
        // Authenticate and retrieve the JWT token
        $response = static::createClient()->request('POST', '/api/auth', [
            'json' => [
                'email' => 'admin@local.host',
                'password' => 'password',
            ],
        ]);

        return $response->toArray()['token']; // Return the token
    }

    public function test_POST_GET_PUT_REMOVE_UserCRole(): void
    {
        $email = 'test@test_email.com';
        $password = 'password';

        // Step 1: Create a new user (POST)
        $response = $this->createClient()->request('POST', '/api/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'email' => $email,
                'password' => password_hash($password, PASSWORD_BCRYPT)
            ],
        ]);

        $user_id = $response->toArray()['id']; // Get the ID of the created user

        // Step 2: Create a new address (POST)
        $response = $this->createClient()->request('POST', '/api/addresses', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'number' => '1',
                'street' => 'rue de la paix',
                'postal_code' => '75000',
                'city' => 'Paris',
                'country' => 'France',
            ],
        ]);

        $address_id = $response->toArray()['id']; // Get the ID of the created address

        // Step 3: Create a new company (POST)
        $response = $this->createClient()->request('POST', '/api/companies', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'name' => 'Company name',
                'siret' => '12345678901255',
                'address' => '/api/addresses/' . $address_id,
                "project" => [],
                "userCompanyRoles" => [],
            ],
        ]);

        $company_id = $response->toArray()['id']; // Get the ID of the created company

        // Step 4: Create a user_company_role (POST)
        $response = $this->createClient()->request('POST', '/api/user_company_roles', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'user' => '/api/users/' . $user_id,
                'company' => '/api/companies/' . $company_id,
                'role' => Role::ADMIN, // Using Role::ADMIN constant
            ],
        ]);

        $this->assertResponseStatusCodeSame(201); // Check if the response status code is 201

        $userCompanyRole_id = $response->toArray()['id']; // Get the ID of the created user_company_role

        // Step 5: Get the user_company_role (GET)
        $response = $this->createClient()->request('GET', '/api/user_company_roles/' . $userCompanyRole_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $this->assertResponseStatusCodeSame(200); // Check if the response status code is 200
        $userCompanyRole = $response->toArray();
        $this->assertSame(Role::ADMIN->value, $userCompanyRole['role']); // Assert that the role is 'ADMIN'

        // Step 6: Update the user_company_role (PUT)
        $newRole = Role::USER;
        $response = $this->createClient()->request('PUT', '/api/user_company_roles/' . $userCompanyRole_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'user' => '/api/users/' . $user_id,
                'company' => '/api/companies/' . $company_id,
                'role' => $newRole,
            ],
        ]);

        $this->assertResponseStatusCodeSame(200); // Check if the response status code is 200

        // Step 7: Verify role change
        $response = $this->createClient()->request('GET', '/api/user_company_roles/' . $userCompanyRole_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $updatedRole = $response->toArray();
        $this->assertSame($newRole->value, $updatedRole['role']); // Assert the role is updated to 'USER'

        // Step 8: Delete the user_company_role (DELETE)
        $response = $this->createClient()->request('DELETE', '/api/user_company_roles/' . $userCompanyRole_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $this->assertResponseStatusCodeSame(204); // Check if the response status code is 204

        // Step 9: Cleanup - Delete user, company, and address
        $response = $this->createClient()->request('DELETE', '/api/users/' . $user_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $response = $this->createClient()->request('DELETE', '/api/companies/' . $company_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $response = $this->createClient()->request('DELETE', '/api/addresses/' . $address_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);
    }
}
