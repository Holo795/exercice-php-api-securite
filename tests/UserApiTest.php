<?php

namespace App\Tests;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;

class UserApiTest extends ApiTestCase
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

    public function test_POST_GET_PUT_REMOVE_User(): void
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
                'password' => $password, // Send password as plaintext
            ],
        ]);

        $this->assertResponseStatusCodeSame(201);
        $user_id = $response->toArray()['id']; // Get the ID of the created user

        // Step 2: Retrieve the user (GET)
        $response = $this->createClient()->request('GET', '/api/users/' . $user_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);

        $userData = $response->toArray();
        $this->assertSame($email, $userData['email']); // Assert the email is correct

        // Step 3: Update the user (PUT)
        $newPassword = 'newPassword';
        $response = $this->createClient()->request('PUT', '/api/users/' . $user_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
                'Content-Type' => 'application/ld+json',
            ],
            'json' => [
                'email' => $email,
                'password' => $newPassword, // Send new plaintext password
            ],
        ]);

        $this->assertResponseStatusCodeSame(200);

        // Step 4: Delete the user (DELETE)
        $response = $this->createClient()->request('DELETE', '/api/users/' . $user_id, [
            'headers' => [
                'Authorization' => 'Bearer ' . $this->token,
            ],
        ]);

        $this->assertResponseStatusCodeSame(204);
    }
}
