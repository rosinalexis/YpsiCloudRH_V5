<?php

namespace App\Controller;

use App\Email\Mailer;
use App\Form\NewPasswordType;
use Symfony\Component\Finder\Finder;
use App\Security\UserConfirmationService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default_index')]
    public function index(): Response
    {
        return $this->render('default/home.html.twig');
    }

    #[Route("/confirm-user/{token}", name: "default_confirm_token")]
    public function confirmUser(string $token, Request $request, UserConfirmationService $userConfirmationService)
    {

        $form = $this->createForm(NewPasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //vérification on ne sait jamais
            $password = $request->request->get("new_password")["plainPassword"]["first"];
            $confirmPassword = $request->request->get("new_password")["plainPassword"]["second"];

            if ($password == $confirmPassword) {
                $userConfirmationService->confirmUser($token, $password);
            }


            return $this->redirect('http://localhost:8081');
        }

        return $this->render('default/index.html.twig', [
            'controller_name' => 'DefaultController',
            'form' => $form->createView()
        ]);
    }


    #[Route("api/meeting/email", name: "default_meeting_email")]
    public function confirmMettingEmail(Mailer $mailer)
    {
        $mailer->sendMeetingMail();

        return new JsonResponse('ok', Response::HTTP_OK);
    }
}
