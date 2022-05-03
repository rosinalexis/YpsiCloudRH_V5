<?php

namespace App\Controller;

use App\Email\Mailer;
use App\Entity\Contact;
use App\Form\NewPasswordType;

use App\Security\UserConfirmationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DefaultController extends AbstractController
{
    #[Route('/', name: 'default_index')]
    public function index(): Response
    {
        return new JsonResponse(
            [
                "title" => "YPSI CLOUD RH V5",
                "version" => "0.5",
                "message" => "Welcome To the backend of Ypsi Cloud RH "
            ],
            Response::HTTP_OK
        );
    }

    #[Route("/confirm-user/{token}", name: "default_confirm_token", methods: ["post"])]
    public function confirmUser(string $token, Request $request, UserConfirmationService $userConfirmationService)
    {

        //vérification des mots de passe 
        $password = $request->request->get("new_password_first");
        $confirmPassword = $request->request->get("new_password_second");


        //si les mots de passe son identique
        if (($password && $confirmPassword) && $password === $confirmPassword) {
            $userConfirmationService->confirmUser($token, $password);
            return new JsonResponse([
                "message" => "mot de passe modifier"
            ], Response::HTTP_ACCEPTED);
        }

        //dans le cas ou il y a une erreur
        return new JsonResponse([
            "error" => "les mots de passe sont invalides.",
            "password" => $password
        ], Response::HTTP_BAD_REQUEST);
    }


    #[Route("api/meeting/email/{id}", name: "default_meeting_email")]
    public function confirmMetingEmail(Contact $contact, Mailer $mailer)
    {
        $mailer->sendMeetingMailV2($contact);

        return new JsonResponse([
            "email" => "is send"
        ], Response::HTTP_OK);
    }

    #[Route("validate/date/{id}/{uid}", name: "default_date_validation")]
    public function contactDateValidation(Contact  $contact, string $uid,  EntityManagerInterface $em)
    {
        $meetingDate = null;

        if ($contact->getManagement()["contactAdministrationMeeting"]["isUserValidation"]) {
            //vérifier si une date n'a pas été sélectionnée.
            foreach ($contact->getManagement()["contactAdministrationMeeting"]["proposedDates"] as $key => $dateValue) {

                if ($dateValue["isOk"]) {
                    $meetingDate = $dateValue["newDate"];
                }
            }
        } else {

            //vérifier aucune date n'a été sélectionnée.
            foreach ($contact->getManagement()["contactAdministrationMeeting"]["proposedDates"] as $key => $dateValue) {

                if ($dateValue["uid"]  == $uid && !$dateValue["isOk"]) {
                    $newManagement = $contact->getManagement();
                    $newManagement["contactAdministrationMeeting"]["proposedDates"][$key]["isOk"] = true;
                    $newManagement["contactAdministrationMeeting"]["isUserValidation"]  = true;

                    $contact->setManagement($newManagement);
                    $contact->setState("Réponse du candidat ok.");
                    $em->flush();

                    $meetingDate = $dateValue["newDate"];
                }
            }
        }

        return new JsonResponse([
            "meetingDate" => $meetingDate
        ], Response::HTTP_OK);

        //return $this->render('email/date_validation/user_date_validation.html.twig', compact('meetingDate'));
    }
}
