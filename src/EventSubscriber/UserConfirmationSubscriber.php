<?php

namespace App\EventSubscriber;

use App\Entity\UserConfirmation;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use App\Security\UserConfirmationService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

final class UserConfirmationSubscriber implements EventSubscriberInterface
{

    /**
     * @var UserConfirmationService
     */
    private UserConfirmationService $userConfirmationService;

    public function __construct(
        UserConfirmationService $userConfirmationService
    ) {
        $this->userConfirmationService =  $userConfirmationService;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['confirmUser', EventPriorities::POST_VALIDATE],
        ];
    }


    public function confirmUser(ViewEvent $event)
    {
        /** @var UserConfirmation $userConfirmationToken*/
        $userConfirmationToken = $event->getControllerResult();

        $method = $event->getRequest()->getMethod();

        if (!$userConfirmationToken instanceof UserConfirmation || Request::METHOD_POST !== $method) {
            return;
        }

        $this->userConfirmationService->confirmUser($userConfirmationToken->confirmationToken);


        $event->setResponse(new JsonResponse(null, Response::HTTP_OK));
    }
}
