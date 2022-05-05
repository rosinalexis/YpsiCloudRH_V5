<?php

namespace App\Action;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class CurrentUserAction extends AbstractController
{
    private  $user;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->user  = $tokenStorage->getToken()->getUser();
    }

    public function __invoke(): JsonResponse
    {
        return $this->json($this->user);
    }
}
