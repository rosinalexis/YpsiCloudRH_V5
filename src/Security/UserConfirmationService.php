<?php

namespace App\Security;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserConfirmationService
{
    /**
     * @var UserRepository
     */
    private $userRepo;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $em
    ) {
        $this->userRepo = $userRepository;
        $this->em = $em;
    }

    public function confirmUser(string $confirmationToken)
    {

        $user = $this->userRepo->findOneBy(
            ['confirmationToken' => $confirmationToken]
        );

        if (!$user) {
            throw new NotFoundHttpException("This token has already been used or not exist.Pls contact your admin.");
        }

        $user->setIsActivated(true);
        $user->setConfirmationToken(null);

        $this->em->flush();
    }
}
