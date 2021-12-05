<?php

namespace App\Tests\Api;

use App\Entity\Category;
use App\Entity\JobAdvert;
use PHPUnit\Framework\MockObject\Api;
use Symfony\Component\HttpFoundation\Response;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class JobAdvertApiTest extends ApiTestCase
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

    public function testJobAdvertApiGetCollection(): void
    {
        $response = $this->sendRequest('GET');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(5, $response->toArray()['hydra:member']);
    }

    public function testJobAdvertApiPostItem(): void
    {
        $jobAdvert = $this->getEntity();

        //on vérifie que l'utilisateur a bien été créé
        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertArrayHasKey('id', $jobAdvert);
    }


    public function testJobAdvertApiUpdateItem(): void
    {
        $this->getEntity();
        $uri = $this->findIriBy(JobAdvert::class, ['title' => 'Concepteur']);
        $response = $this->client->request('PUT', $uri, [
            'headers' => [
                'Content-Type' => 'application/ld+json'
            ],
            'auth_bearer' => $this->token,
            'json' => [
                'title' => 'Concepteur Updated'
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertEquals('concepteur updated', $response->toArray()['title']);
    }

    public function testJobApiDeleteItem(): void
    {
        $this->getEntity();
        $uri = $this->findIriBy(JobAdvert::class, ['title' => 'concepteur']);

        $response = $this->sendRequest("DELETE", $uri);
        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(JobAdvert::class)->findOneBy(['title' => 'concepteur'])
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
        $category = $this->sendRequest('POST', "/api/categories", $newCategory);

        $categoryUri = $this->findIriBy(Category::class, ['title' => "CategoryTest1"]);

        //définition de l'utilisateur
        $newJobAdvert = [
            "title" => "Concepteur",
            "place" => "Algérie",
            "compagny" => "YPSI",
            "contractType" => "ITERIM",
            "wage" => "1461€",
            "description" => "Bray, tandis que, du côté des champs. Il y eut quelques réclamations; elle les touchait! -- ni des sifflets de vermeil pour ses cataplasmes, et le soleil, avaient la couleur du cidre doux, et ils se.",
            "published" => true,
            "tasks" => [
                "task1",
                "task2",
                "task3"
            ],
            "requirements" => [
                "requirement1",
                "requirement2",
                "requirement3"
            ],

            "category" => $categoryUri
        ];

        //test ajouter un utilisateur
        $response = $this->sendRequest('POST', null, $newJobAdvert);
        $data  = $response->toArray();

        return $data;
    }



    private function sendRequest(string $method, string $uri = null, $data = [])
    {
        $uri ? $uri : ($uri = '/api/job_adverts');

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
