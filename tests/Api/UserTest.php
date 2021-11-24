<?php

namespace App\Tests\Api;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Response;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class UserTest extends ApiTestCase
{
    private $client;
    private $token;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->token = $this->getToken();
    }


    private function getToken($body = []): string
    {
        if ($this->token) {
            return $this->token;
        }

        $response = $this->client->request(
            'POST',
            '/authentication_token',
            [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => $body ?: [
                    'username' => 'admin@admin.fr',
                    'password' => '123456',
                ]
            ]
        );

        $data = $response->toArray();
        $this->token = $data['token'];
        // $data = json_decode($response->getContent());
        // $this->token = $data->token;

        return $data['token'];
    }

    public function testCreateValidUser(): void
    {

        //définition de l'utilisateur
        $newUser = [
            'email' => 'user@test.fr',
            'password' => '123456',
            'isActivated' => true,
            'roles' => ["ROLE_USER"]
        ];

        //test ajouter un utilisateur
        $response = $this->client->request('POST', '/api/users', ['auth_bearer' => $this->token, 'json' => $newUser]);

        $data  = $response->toArray();

        //on vérifie que l'utilisateur a bien été créé
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertArrayHasKey('id', $data);
    }

    public function testGetUserItem()
    {
        $uri = $this->findIriBy(User::class, ['email' => 'admin@admin.fr']);

        $this->client->request('GET', $uri, ['headers' => ['Content-Type' => 'application/json'], 'auth_bearer' => $this->token]);

        $this->assertResponseIsSuccessful();
    }
}
