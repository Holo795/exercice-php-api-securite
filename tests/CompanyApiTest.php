<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class CompanyApiTest extends ApiTestCase
{
    private string $token; // JWT token for authentication

    // Set up method to initialize the test environment and get the authentication token
    protected function setUp(): void
    {
        parent::setUp();
        $this->token = $this->getToken();
    }

    // Helper function to get the authentication token using admin credentials
    private function getToken(): string
    {
        $response = static::createClient()->request('POST', '/api/auth', [
            'json' => [
                'email' => 'admin@local.host',
                'password' => 'password',
            ],
        ]);

        return $response->toArray()['token']; // Return the token
    }

    // Test to create a company, retrieve its details, and delete it
    public function test_GET_Companies(): void
    {
        // Creating an address first
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

        // Creating a company with the address
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

        // Fetching the created company details
        $response = $this->createClient()->request('GET', '/api/companies/'. $company_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $this->assertResponseStatusCodeSame(200); // Ensure the response status is OK

        // Deleting the created company
        $response = $this->createClient()->request('DELETE', '/api/companies/' . $company_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);
    }

    // Test to create and delete a company, ensuring proper response codes
    public function test_POST_DELETE_Company(): void
    {
        // Creating an address
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

        $this->assertNotNull($address_id); // Ensure address ID is not null
        $this->assertResponseStatusCodeSame(201); // Ensure address creation was successful

        // Creating a company
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

        $this->assertNotNull($company_id); // Ensure company ID is not null
        $this->assertResponseStatusCodeSame(201); // Ensure company creation was successful

        // Deleting the created company
        $response = $this->createClient()->request('DELETE', '/api/companies/' . $company_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $this->assertResponseStatusCodeSame(204); // Ensure successful deletion
    }

    // Test that a non-admin user cannot delete a company
    public function test_NoAdmin_DELETE_Company(): void
    {
        // Creating an address
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

        // Creating a company
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

        // Creating a non-admin user
        $noAdminEmail = 'test@test_email.com';
        $noAdminPassword = 'password';

        $response = $this->createClient()->request('POST', '/api/users', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'email' => $noAdminEmail,
                'password' => password_hash($noAdminPassword, PASSWORD_BCRYPT),
            ],
        ]);

        $user_id = $response->toArray()['id']; // Get the ID of the created user

        $this->assertNotNull($user_id); // Ensure user ID is not null
        $this->assertResponseStatusCodeSame(201); // Ensure user creation was successful

        // Authenticating the non-admin user
        $response = $this->createClient()->request('POST', '/api/auth', [
            'json' => [
                'email' => $noAdminEmail,
                'password' => $noAdminPassword,
            ],
        ]);

        $noAdminToken = $response->toArray()['token']; // Get the token of the non-admin user

        // Attempting to delete the company with the non-admin token
        $response = $this->createClient()->request('DELETE', '/api/companies/' . $company_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $noAdminToken,
            ],
        ]);

        $this->assertResponseStatusCodeSame(403); // Ensure non-admin cannot delete the company

        // Deleting the company with the admin token
        $response = $this->createClient()->request('DELETE', '/api/companies/' . $company_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        // Deleting the user and address after the test
        $response = $this->createClient()->request('DELETE', '/api/users/' . $user_id, [
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

    // Test to update a company
    public function test_PUT_Company(): void
    {
        // Creating an address
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

        // Creating a company
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

        // Updating the company
        $response = $this->createClient()->request('PUT', '/api/companies/' . $company_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'name' => 'New company name',
                'siret' => '12345678901255',
                'address' => '/api/addresses/' . $address_id,
                "project" => [],
                "userCompanyRoles" => [],
            ],
        ]);

        $this->assertNotNull($response->toArray()['id']); // Ensure company ID is not null
        $this->assertResponseStatusCodeSame(200); // Ensure the response status is OK

        // Deleting the company and address after the test
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
