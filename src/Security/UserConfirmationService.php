<?php

namespace App\Security;

use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

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

    /**
     * @var PasswordHasherInterface
     */
    private $passwordHasher;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->userRepo = $userRepository;
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    public function confirmUser(string $confirmationToken, string $plainPassword)
    {

        $user = $this->userRepo->findOneBy(
            ['confirmationToken' => $confirmationToken]
        );

        if (!$user) {
            throw new NotFoundHttpException("This token has already been used or not exist.Pls contact your admin.");
        }

        $user->setIsActivated(true);
        $user->setConfirmationToken(null);

        // hash du mot de passe de l'utilisateur
        $user->setPassword(
            $this->passwordHasher->hashPassword(
                $user,
                $plainPassword
            )
        );

        $this->em->flush();

        return $user;
    }
}
