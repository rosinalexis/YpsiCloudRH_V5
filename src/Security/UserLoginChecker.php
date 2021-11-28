<?php

namespace App\Security;

use App\Entity\User;

use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\Exception\DisabledException;
use Symfony\Component\Security\Core\User\UserInterface;

class UserLoginChecker implements UserCheckerInterface
{
    public function checkPreAuth(UserInterface $user)
    {

        if (!$user instanceof User) {
            return;
        }

        if (!$user->getIsActivated()) {
            throw new CustomUserMessageAccountStatusException("Your user account is disable.Please contact your admin.");
        }
    }

    public function checkPostAuth(UserInterface $user)
    {
    }
}
