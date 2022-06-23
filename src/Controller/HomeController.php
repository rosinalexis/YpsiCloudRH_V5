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

class HomeController extends AbstractController
{
    #[Route('/', name: 'default_index', methods: ["GET"])]
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

    #[Route("/confirm-user/{token}", name: "default_confirm_token", methods: ["POST"])]
    public function confirmUserAccount(string $token, Request $request, UserConfirmationService $userConfirmationService): JsonResponse
    {
        $reponse  = new JsonResponse();

        //vérification des mots de passe 
        $password = $request->request->get("new_password_first");
        $confirmPassword = $request->request->get("new_password_second");

        if (($password && $confirmPassword) && ($password === $confirmPassword)) {
            $userConfirmationService->confirmUser($token, $password);
            $reponse->setData([
                "message" => "mot de passe modifier"
            ]);
            $reponse ->setStatusCode(Response::HTTP_ACCEPTED);
        }else {
            $reponse->setData([
                "message" => "mot de passe modifier"
            ]);
            $reponse->setStatusCode(Response::HTTP_CONFLICT);
        }

       return $reponse;
    }

    #[Route("api/meeting/email/{id}", name: "default_meeting_email")]
    public function sendMeetingDatesToContact(Contact $contact, Mailer $mailer): JsonResponse
    {
        $mailer->sendMeetingMailV2($contact);

        return new JsonResponse([
            "email" => "is send"
        ], Response::HTTP_OK);
    }

    #[Route("validate/date/{id}/{uid}", name: "default_date_validation")]
    public function validateOneMeetingDate(Contact  $contact, string $uid,  EntityManagerInterface $em): JsonResponse
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

    #[Route("/add/admin/user", name: "default_add_admin_user", methods: ["POST"])]
    public function addNewAdminUser(Request $request, EntityManagerInterface $em){
        dd($request);
    }
}
