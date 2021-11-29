<?php

namespace App\EventSubscriber;

use App\Entity\User;
use App\Entity\UserConfirmation;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use ApiPlatform\Core\EventListener\EventPriorities;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class UserConfirmationSubscriber implements EventSubscriberInterface
{
    private $userRepository;
    private $em;

    public function __construct(
        UserRepository $userRepository,
        EntityManagerInterface $em
    ) {
        $this->userRepository = $userRepository;
        $this->em = $em;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => ['confirmUser', EventPriorities::POST_VALIDATE],
        ];
    }


    public function confirmUser(ViewEvent $event)
    {
        /** @var UserConfirmation $userConfirmation*/
        $userConfirmation = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$userConfirmation instanceof UserConfirmation || Request::METHOD_POST !== $method) {
            return;
        }

        /** @var User $user */
        $user = $this->userRepository->findOneBy(['confirmationToken' => $userConfirmation->confirmationToken]);

        if (!$user) {
            throw new NotFoundHttpException("This token has already been used or not exist.Pls contact your admin.");
        }

        $user->setIsActivated(true);
        $user->setConfirmationToken(null);
        $this->em->flush();

        $event->setResponse(new JsonResponse(null, Response::HTTP_OK));
    }
}
