<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SecurityController extends AbstractController
{
    #[Route('/api/logout', name: 'app_logout', methods: ["GET"])]
    public function logout(): Response
    {
        $response = new Response();
        $response->headers->clearCookie('BEARER', '/', null, true, true, 'none');

        return $response;
    }
}
