<?php

namespace App\EventListener;

use Exception;
use Symfony\Component\HttpFoundation\Cookie;
use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;


/**
 * Class JWTAuthenticationSuccessListener
 * @package App\EventListener
 */
class JWTAuthenticationSuccessListener
{
    /**
     * @var int
     */
    private int $tokenLifetime;

    public function __construct(int $tokenLifetime)
    {
        $this->tokenLifetime = $tokenLifetime;
    }

    /**
     * Sets JWT as a cookie on successful authentication.
     * 
     * @param AuthenticationSuccessEvent $event
     * @throws Exception
     */
    public function onAuthenticationSuccess(AuthenticationSuccessEvent $event): void
    {

        $response = $event->getResponse();
        $data = $event->getData();

        $token = $data['token'];

        unset($data['token']);
        $event->setData($data);

        $response->headers->setCookie(
            new Cookie(
                'BEARER', // Cookie name, should be the same as in config/packages/lexik_jwt_authentication.yaml.
                $token, // cookie value
                time() + $this->tokenLifetime, // expiration
                '/', // path
                null, // domain, null means that Symfony will generate it on its own.
                true, // secure
                true, // httpOnly
                false, // raw
                'none' // same-site parameter, can be 'lax' or 'strict'.
            )
        );
    }
}
