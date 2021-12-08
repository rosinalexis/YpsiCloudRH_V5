<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Profile;
use phpDocumentor\Reflection\Types\Null_;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class ProfileApiTest extends ApiTestCase
{
    private $client;
    private $token;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->token = $this->getToken();
    }

    private function getToken($body = [])
    {
        if ($this->token) {
            return $this->token;
        }

        $response = $this->client->request(
            'POST',
            '/api/login',
            [
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'json' => ($body ?: [
                    'username' => 'admin@admin.fr',
                    'password' => '123456',
                ])
            ]
        );

        // $data = $response->toArray();
        // $this->token = $data['token'];
        // // $data = json_decode($response->getContent());
        // // $this->token = $data->token;

        // return $data['token'];
    }

    public function testProfileApiGetCollection(): void
    {
        $response = $this->sendRequest('GET');
        $this->assertResponseStatusCodeSame(Response::HTTP_METHOD_NOT_ALLOWED);
    }

    public function testProfileApiPostItem(): void
    {
        $newProfile = [
            'lastname' => 'lastnameTest',
            'firstname' => 'firstnameTest',
            'gender' => 'monsieur',
            'phone' => '0202020202',
            'address' => '2 rue de testrue 79000 testville',
            'birthdate' => '1999-01-12',
            'description' => 'description de test'
        ];

        $response = $this->sendRequest('POST', NULL, $newProfile);
        $data = $response->toArray();

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertArrayHasKey('id', $data);
        $this->assertArrayHasKey('createdAt', $data);
        $this->assertArrayHasKey('lastname', $data);
        $this->assertMatchesResourceItemJsonSchema(Profile::class);
    }

    public function testProfileApiGetItem(): void
    {
        //création et enregistrement d'un nouveau profile 
        $this->createNewProfile();

        //récupération du profile
        $uri = $this->findIriBy(Profile::class, ['lastname' => 'lastnameTest']);
        $response = $this->sendRequest('GET', $uri);
        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'lastname' => 'lastnametest',
        ]);
        $this->assertMatchesRegularExpression('~^/api/profiles/\d+$~', $response->toArray()['@id']);
        $this->assertMatchesResourceItemJsonSchema(Profile::class);
    }


    public function testProfileApiUpdateItem(): void
    {
        //création et enregistrement d'un nouveau profile 
        $testProfile = $this->createNewProfile();

        //récupération du profile
        $uri = $this->findIriBy(Profile::class, ['lastname' => 'lastnameTest']);

        $response = $this->client->request('PUT', $uri, [
            'headers' => [
                'Content-Type' => 'application/ld+json'
            ],
            'auth_bearer' => $this->token,
            'json' => [
                'description' => 'Update description.'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertSame('update description.', $response->toArray()['description']);
    }


    private function createNewProfile(): Profile
    {
        $serializer = static::getContainer()->get('serializer');

        //création et enregistrement d'un nouveau profile 
        $newProfile = [
            'lastname' => 'lastnameTest',
            'firstname' => 'firstnameTest',
            'gender' => 'monsieur',
            'phone' => '0202020202',
            'address' => '2 rue de testrue 79000 testville',
            'birthdate' => '1999-01-12',
            'description' => 'description de test'
        ];

        $profile = $serializer->deserialize(json_encode($newProfile), Profile::class, 'json');
        $response = $this->sendRequest('POST', NULL, $newProfile);

        return $profile;
    }


    private function sendRequest(string $method, string $uri = null, $data = [])
    {
        $uri ? $uri : ($uri = '/api/profiles');

        if (!$data) {
            $data = [];
        }

        $response = $this->client->request($method, $uri, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            // 'auth_bearer' => $this->token,
            'json' => $data
        ]);

        return $response;
    }
}
