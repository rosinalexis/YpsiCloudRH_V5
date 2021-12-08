<?php

namespace App\Tests\Api;

use App\Entity\Contact;
use App\Entity\Category;
use App\Entity\JobAdvert;
use Symfony\Component\HttpFoundation\Response;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;

class ContactApiTest extends ApiTestCase
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
        // return $data['token'];
    }

    public function testContactApiGetCollection(): void
    {
        $response = $this->sendRequest('GET');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        $this->assertCount(0, $response->toArray()['hydra:member']);
    }

    public function testContactApiPostItem(): void
    {
        $contact = $this->getEntity();

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertArrayHasKey('id', $contact);
    }

    public function testContactApiUpdateItem(): void
    {
        $this->getEntity();
        $uri = $this->findIriBy(Contact::class, ["firstname" => "firstnameTestContact"]);
        $response = $this->client->request('PUT', $uri, [
            'headers' => [
                'Content-Type' => 'application/ld+json'
            ],
            'auth_bearer' => $this->token,
            'json' => [
                "management" => [
                    "contactAdministrationHelp" => [
                        "status" => true,
                        "helpList" => []
                    ],
                    "contactAdministrationMeeting" => [
                        "status" => null,
                        "supervisor" => null
                    ],
                    "contactAdministrationContract" => [
                        "status" => null
                    ],
                    "contactAdministrationDocument" => [
                        "status" => null,
                        "documentList" => []
                    ],
                    "contactAdministrationEquipement" => [
                        "status" => null,
                        "equipementList" => []
                    ],
                    "contactAdministrationValidation" => [
                        "status" => null,
                        "supervisor" => null
                    ]
                ]
            ]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testContactApiDeleteItem(): void
    {
        $this->getEntity();
        $uri = $this->findIriBy(Contact::class, ['firstname' => "firstnametestcontact"]);

        $response = $this->sendRequest("DELETE", $uri);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
        $this->assertNull(
            static::getContainer()->get('doctrine')->getRepository(Contact::class)->findOneBy(['firstname' => 'firstnametestcontact'])
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
        $category = $this->sendRequest('POST', '/api/categories', $newCategory);

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
        $jobAdvert = $this->sendRequest('POST', '/api/job_adverts', $newJobAdvert);

        $uriJobAdvert = $this->findIriBy(JobAdvert::class, ['title' => 'concepteur']);

        $newContact = [
            'firstname' => "firstnameTestContact",
            'lastname' => "lastnameTestContact",
            'email' => "emailTestContact",
            'subject' => "subjectTestContact",
            'message' => "messageTestContact",
            'jobReference' => $uriJobAdvert
        ];

        $response = $this->sendRequest('POST', null, $newContact);

        $data  = $response->toArray();

        return $data;
    }

    private function sendRequest(string $method, string $uri = null, $data = [])
    {
        $uri ? $uri : ($uri = '/api/contacts');

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
