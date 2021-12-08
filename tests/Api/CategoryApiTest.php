<?php

namespace App\Tests\Api;

use App\Entity\Category;
use Symfony\Component\HttpFoundation\Response;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class CategoryApiTest extends ApiTestCase
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
    }

    public function testCategoryApiGetCollection(): void
    {
        $response = $this->sendRequest('GET');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(5, $response->toArray()['hydra:member']);
    }

    public function testCategoryApiPostItem(): void
    {
        $category = $this->getEntity();

        //on vérifie que l'utilisateur a bien été créé
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertArrayHasKey('id', $category);
    }

    public function testCategoryApiGetItem(): void
    {
        $category  = $this->getEntity();

        $uri = $this->findIriBy(Category::class, ['title' => $category["title"]]);

        $response = $this->sendRequest('GET', $uri);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertJsonContains([
            'title' => $category["title"]
        ]);
        $this->assertResponseHeaderSame('content-type', 'application/ld+json; charset=utf-8');
        $this->assertMatchesRegularExpression('~^/api/categories/\d+$~', $response->toArray()['@id']);
    }

    public function testCategoryApiUpdateItem(): void
    {
        $category  = $this->getEntity();

        $uri = $this->findIriBy(Category::class, ['title' => $category["title"]]);

        $response = $this->client->request('PUT', $uri, [
            'headers' => [
                'Content-Type' => 'application/ld+json'
            ],
            // 'auth_bearer' => $this->token,
            'json' => [
                'title' => 'categorytitle Updated'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals('categorytitle updated', $response->toArray()['title']);
    }

    public function testCategoryApiDeleteItem(): void
    {
        $category  = $this->getEntity();

        $uri = $this->findIriBy(Category::class, ['title' => $category["title"]]);

        $response = $this->sendRequest("DELETE", $uri);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Category::class)->findOneBy(['title' => $category["title"]])
        );
    }


    private function getEntity()
    {
        //définition de l'utilisateur
        $newCategory = [
            'title' => 'CategoryTest1',
            'description' => 'Description job test 1',
        ];

        //test ajouter un utilisateur
        $response = $this->sendRequest('POST', null, $newCategory);
        $data  = $response->toArray();

        return $data;
    }


    private function sendRequest(string $method, string $uri = null, $data = [])
    {
        $uri ? $uri : ($uri = '/api/categories');

        if (!$data) {
            $data = [];
        }

        $response = $this->client->request($method, $uri, [
            'headers' => [
                'Content-Type' => 'application/json'
            ],
            //'auth_bearer' => $this->token,
            'json' => $data
        ]);

        return $response;
    }
}
