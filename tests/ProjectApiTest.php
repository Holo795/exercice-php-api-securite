<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class ProjectApiTest extends ApiTestCase
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

    public function test_GET_POST_REMOVE_Project(): void
    {
        // Step 1: Create Address
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

        // Step 2: Create Company
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

        // Step 3: Test POST Project
        $response = $this->createClient()->request('POST', '/api/projects', [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'title' => 'Project Title',
                'description' => 'Project Description',
                'company' => '/api/companies/' . $company_id,
            ],
        ]);

        $this->assertResponseStatusCodeSame(201); // Check if the response status code is 201

        $project_id = $response->toArray()['id']; // Get the ID of the created project

        // Step 4: Test GET Project
        $response = $this->createClient()->request('GET', '/api/projects/' . $project_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        // Assert the project title and description are correct
        $projectData = $response->toArray();
        $this->assertSame('Project Title', $projectData['title']);
        $this->assertSame('Project Description', $projectData['description']);

        // Step 5: Test DELETE Project
        $this->assertResponseStatusCodeSame(200); // Check if the response status code is 200

        $response = $this->createClient()->request('DELETE', '/api/projects/' . $project_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $this->assertResponseStatusCodeSame(204); // Check if the response status code is 204

        // Step 6: Clean up by deleting the company and address
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
