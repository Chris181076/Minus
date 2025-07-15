<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Uid\Uuid;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\TransportInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Form\SetPasswordForm;


class EmailController extends AbstractController
{
    #[Route('/api/create-user', name: 'api_create_user', methods: ['POST'])]
    public function createUserAndSendActivation(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        LoggerInterface $logger
    ): Response {
        $data = json_decode($request->getContent(), true);

        $requiredFields = ['email', 'firstName', 'lastName'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                return $this->json(['error' => "Le champ '$field' est requis"], Response::HTTP_BAD_REQUEST);
            }
        }

        $existingUser = $em->getRepository(User::class)->findOneBy(['email' => $data['email']]);
        if ($existingUser) {
            return $this->json(['error' => 'Un utilisateur avec cet email existe déjà'], Response::HTTP_CONFLICT);
        }

        try {
            $user = new User();
            $user->setEmail($data['email']);
            $user->setFirstName($data['firstName']);
            $user->setLastName($data['lastName']);
            if (isset($data['phone'])) {
                $user->setPhoneNumber($data['phone']);
            }

            $activationToken = Uuid::v4()->toRfc4122();
            $user->setActivationToken($activationToken);
            $user->setIsActive(false);
            $user->setPassword('');
            $user->setRoles(['ROLE_PARENT']);
            $user->setCreatedAt(new \DateTimeImmutable());

            $em->persist($user);
            $em->flush();

            $activationUrl = $this->generateUrl(
                'app_user_activate',
                ['token' => $activationToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $emailMessage = (new Email())
                ->from('no-reply@minus.fr')
                ->to($user->getEmail())
                ->subject('Activation de votre compte')
                ->html("
                    <h2>Bienvenue !</h2>
                    <p>Bonjour {$user->getFirstName()} {$user->getLastName()},</p>
                    <p>Votre compte a été créé avec succès. Cliquez ci-dessous pour l'activer :</p>
                    <p><a href=\"$activationUrl\" style=\"background-color: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;\">Activer mon compte</a></p>
                    <p>Si le bouton ne fonctionne pas, copiez/collez ce lien : $activationUrl</p>
                ");

            $logger->info("Envoi de l'email d'activation à {$user->getEmail()}");
            $mailer->send($emailMessage);
            $logger->info("Email envoyé avec succès");

            return $this->json([
                'message' => 'Utilisateur créé et email envoyé',
                'user' => [
                    'id' => $user->getId(),
                    'email' => $user->getEmail(),
                    'firstName' => $user->getFirstName(),
                    'lastName' => $user->getLastName(),
                ]
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            $logger->error("Erreur d'envoi de mail : " . $e->getMessage());
            return $this->json([
                'error' => 'Erreur lors de la création de l\'utilisateur',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /*#[Route('/api/resend-activation/{id}', name: 'api_resend_activation', methods: ['POST'])]
    public function resendActivation(
        int $id,
        EntityManagerInterface $em,
        MailerInterface $mailer,
        LoggerInterface $logger
    ): Response {
        $user = $em->getRepository(User::class)->find($id);

        if (!$user) {
            return $this->json(['error' => 'Utilisateur non trouvé'], Response::HTTP_NOT_FOUND);
        }

        if ($user->isActive()) {
            return $this->json(['message' => 'Ce compte est déjà activé'], Response::HTTP_OK);
        }

        try {
            if (!$user->getActivationToken()) {
                $activationToken = Uuid::v4()->toRfc4122();
                $user->setActivationToken($activationToken);
                $em->flush();
            }

            $activationUrl = $this->generateUrl(
                'app_user_activate',
                ['token' => $user->getActivationToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $emailMessage = (new Email())
                ->from('no-reply@minus.fr')
                ->to($user->getEmail())
                ->subject('Rappel : activation de votre compte')
                ->html("
                    <h2>Rappel</h2>
                    <p>Bonjour {$user->getFirstName()} {$user->getLastName()},</p>
                    <p>Votre compte n'est pas encore activé. Cliquez ci-dessous pour l'activer :</p>
                    <p><a href=\"$activationUrl\">Activer mon compte</a></p>
                ");

            $logger->info("Envoi de rappel à {$user->getEmail()}");
            $mailer->send($emailMessage);

            return $this->json([
                'message' => 'Email de rappel envoyé avec succès'
            ]);

        } catch (\Exception $e) {
            $logger->error("Erreur lors de l'envoi de l'email : " . $e->getMessage());
            return $this->json([
                'error' => 'Erreur lors de l\'envoi de l\'email',
                'details' => $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }*/




#[Route('/test-mail', name: 'test_mail')]
    public function testMail(MailerInterface $mailer, LoggerInterface $logger): JsonResponse
    {
        try {
            $email = (new Email())
                ->from('no-reply@minus.fr')
                ->to('test@example.com') // Remplace par une adresse valide si besoin
                ->subject('Test depuis le contrôleur')
                ->text('Ceci est un email de test envoyé via Symfony Mailer.');

            $mailer->send($email);
            $logger->info("Email de test envoyé à test@example.com");

            return $this->json([
                'message' => 'Email de test envoyé avec succès',
                'to' => 'test@example.com'
            ]);
        } catch (\Throwable $e) {
            $logger->error('Erreur lors de l’envoi de l’email : ' . $e->getMessage());

            return $this->json([
                'error' => 'Échec de l’envoi de l’email',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}



