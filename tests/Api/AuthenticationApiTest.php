<?php

namespace App\Tests\Api;

use App\Entity\User;
use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationApiTest extends ApiTestCase
{

    public function testApiUserLogin(): void
    {
        $client = self::createClient();

        $user = new User();
        $user->setEmail('admin@test.fr');
        $user->setRoles(['ROLE_ADMIN']);
        $user->setPassword(self::getContainer()->get('security.user_password_hasher')->hashPassword($user, '123456'));
        $user->setIsActivated(true);

        $manager  = self::getContainer()->get('doctrine')->getManager();
        $manager->persist($user);
        $manager->flush();

        //test de la récuperation d'un token
        $response = $client->request('POST', '/api/login', [
            'headers' => ['Content-type' => 'application/json'],
            'json' => [
                'username' => 'admin@test.fr',
                'password' => '123456',
            ],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_NO_CONTENT);
    }
}
