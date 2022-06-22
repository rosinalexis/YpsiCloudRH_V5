<?php

namespace App\Security;

use App\Entity\User;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\ExpiredTokenException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authentication\Token\PreAuthenticationJWTUserToken;

class TokenAuthenticator extends JWTTokenAuthenticator
{
    /**
     * Undocumented function
     *
     * @param PreAuthenticationJWTUserToken $preAuthToken
     * @param UserProviderInterface $userProvider
     * @return null|UserInterface|void
     */
    public function getUser($preAuthToken, UserProviderInterface $userProvider)
    {
        /**
         * @var User $user
         */
        $user = parent::getUser($preAuthToken, $userProvider);

        if (
            $user->getPasswordChangeDate() &&
            $preAuthToken->getPayload()['iat'] < $user->getPasswordChangeDate()
        ) {
            throw new ExpiredTokenException();
        }

        if (!$user->getIsActivated()) {
            throw new CustomUserMessageAccountStatusException("Your user account is disable.Please contact your admin.");
        }

        return $user;
    }
}
