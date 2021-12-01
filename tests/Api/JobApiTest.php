<?php

namespace App\Tests\Api;

use App\Entity\Job;
use Symfony\Component\HttpFoundation\Response;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class JobApiTest extends ApiTestCase
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
        $data = $response->toArray();
        $this->token = $data['token'];
        return $data['token'];
    }

    public function testJobApiGetCollection(): void
    {
        $response = $this->sendRequest('GET');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(5, $response->toArray()['hydra:member']);
    }

    public function testJobApiPostItem(): void
    {
        $job = $this->getEntity();

        //on vérifie que l'utilisateur a bien été créé
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertArrayHasKey('id', $job);
    }

    public function testJobApiGetItem(): void
    {
        $job  = $this->getEntity();

        $uri = $this->findIriBy(Job::class, ['title' => $job["title"]]);

        $response = $this->sendRequest('GET', $uri);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'title' => $job["title"]
        ]);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertMatchesRegularExpression('~^/api/jobs/\d+$~', $response->toArray()['@id']);
    }

    public function testJobApiUpdateItem(): void
    {
        $job  = $this->getEntity();

        $uri = $this->findIriBy(Job::class, ['title' => $job["title"]]);

        $response = $this->client->request('PUT', $uri, [
            'headers' => [
                'Content-Type' => 'application/ld+json'
            ],
            'auth_bearer' => $this->token,
            'json' => [
                'title' => 'jobtitle Updated'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals('jobtitle updated', $response->toArray()['title']);
    }

    public function testJobApiDeleteItem(): void
    {
        $job  = $this->getEntity();

        $uri = $this->findIriBy(Job::class, ['title' => $job["title"]]);
        $response = $this->sendRequest("DELETE", $uri);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Job::class)->findOneBy(['title' => $job["title"]])
        );
    }

    private function getEntity()
    {
        //définition de l'utilisateur
        $newJob = [
            'title' => 'JobTest1',
            'description' => 'Description job test 1',
        ];

        //test ajouter un utilisateur
        $response = $this->sendRequest('POST', null, $newJob);
        $data  = $response->toArray();

        return $data;
    }


    private function sendRequest(string $method, string $uri = null, $data = [])
    {
        $uri ? $uri : ($uri = '/api/jobs');

        if (!$data) {
            $data = [];
        }

        $response = $this->client->request($method, $uri, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            'auth_bearer' => $this->token,
            'json' => $data
        ]);

        return $response;
    }
}
