<?php

namespace App\Action;

use ApiPlatform\Core\Validator\ValidatorInterface;
use App\Controller\ValidartorInterface;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class ResetPasswordAction extends AbstractController
{
    /**
     * @var ValidartorInterface
     */
    private $_validator;

    /**
     * @var UserPasswordHasherInterface
     */
    private $_userPasswordHasher;

    /**
     * @var EntityManagerInterface
     */
    private $_em;

    /**
     * @var JWTTokenManagerInterface
     */
    private $_tokenManager;

    public function __construct(
        ValidatorInterface $validator,
        UserPasswordHasherInterface $hasher,
        EntityManagerInterface $em,
        JWTTokenManagerInterface $tokenManager
    ) {
        $this->_validator = $validator;
        $this->_userPasswordHasher = $hasher;
        $this->_em = $em;
        $this->_tokenManager = $tokenManager;
    }

    public function __invoke(User $data): JsonResponse
    {
        // validation du mot de passe
        $this->_validator->validate($data);

        // hash du nouveau mot de passe
        $data->setPassword(
            $this->_userPasswordHasher->hashPassword(
                $data,
                $data->getNewPassword()
            )
        );

        $data->setPasswordChangeDate(time());

        //modification dans la base
        $this->_em->flush();

        $token = $this->_tokenManager->create($data);

        return new JsonResponse(['token' => $token]);
    }
}
