<?php

namespace App\Security;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class UserConfirmationService
{
    /**
     * @var UserRepository
     */
    private UserRepository $userRepo;

    /**
     * @var EntityManagerInterface
     */
    private EntityManagerInterface $em;

    /**
     * @var PasswordHasherInterface|UserPasswordHasherInterface
     */
    private PasswordHasherInterface|UserPasswordHasherInterface $passwordHasher;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->userRepo = $userRepository;
        $this->em = $em;
        $this->passwordHasher = $passwordHasher;
    }

    public function confirmUser(string $confirmationToken, string $plainPassword): User
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
