<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Child;
use App\Repository\ChildRepository;
use App\Repository\PlannedPresenceRepository;
use App\Repository\JournalRepository;
use App\Entity\Journal;
use App\Repository\ChildPresenceRepository;
use App\Twig\GlobalVariables;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\User;
use App\Form\UserForm;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Mailer\MailerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Mime\Email;
use App\Repository\SemainierRepository;
use App\Entity\Semainier;
use IntlDateFormatter;


final class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(SemainierRepository $semainierRepository): Response
    {
        $lastSemainier = $semainierRepository->lastSemainier();
         $formatter = new IntlDateFormatter(
            'fr_FR', 
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE  
        );

        $dateDuJour = $formatter->format(new \DateTime());

        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
            'lastSemainier' => $lastSemainier,
            'dateDuJour' => $dateDuJour,
        ]);
    }
 #[Route('/admin/dashboard/child', name: 'app_admin_dashboard_child')]
public function childDashboard(ChildRepository $childRepository): Response
{
    $children = $childRepository->findAll();

    return $this->render('admin/dashboardChild.html.twig', [
        'children' => $children,
    ]);
}
 #[Route('/admin/dashboard/journal', name: 'app_admin_dashboard_journal')]
public function journalDashboard(ChildRepository $childRepository, ChildPresenceRepository $presenceRepo): Response
{
    
    $children = $childRepository->findAll();
    $today = (new \DateTimeImmutable())->setTime(0, 0);
    $presences = [];
    foreach ($children as $child) {
        $presence = $presenceRepo->findOneBy([
            'child' => $child,
            'day' => $today,
        ]);
        $presences[$child->getId()] = $presence;
    }
    return $this->render('admin/dashboardChildJournal.html.twig', [
        'children' => $children,
        'presences' => $presences,
        'presence' => $presence,
    ]);
}

#[Route('/admin/dashboard/journal/{id}', name: 'app_journal_show', methods: ['GET'])]
public function childJournalDashboard(Child $child, JournalRepository $journalRepo): Response
{
    $journals = $journalRepo->findAllJournalByChild($child);

    return $this->render('journal/show.html.twig', [
        'child' => $child,
        'journals' => $journals
    ]);
}

   #[Route('/admin/dashboardChild/{id}', name: 'app_child_show', methods: ['GET'])]
    public function show(Child $child, PlannedPresenceRepository $presenceRepo): Response
    {
        $presences = $presenceRepo->findByChildOrderedByWeekday($child);
        return $this->render('Child/show_admin.html.twig', [
        'child' => $child,
        'presences' => $presences,
    ]);
    }

    #[Route('/admin/user/register', name: 'app_admin_register')]
    public function register(Request $request, EntityManagerInterface $em): Response
    {
    $user = new User();
    $form = $this->createForm(UserForm::class, $user);
    $form->handleRequest($request);
   
    if ($form->isSubmitted() && $form->isValid()) {
        $user->setCreatedAt(new \DateTimeImmutable());
        $user->setActivationToken(Uuid::v4()->toRfc4122());
        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('app_login');
    }

    return $this->render('user/new.html.twig', [
        'form' => $form->createView(),
        'user' => $user,
    ]);
    }

    
    #[Route('/admin/user/create', name: 'parent_create_form')]
    public function showParentCreateForm(
    Request $request,
    EntityManagerInterface $em,
    MailerInterface $mailer,
    LoggerInterface $logger
    ): Response {
    $user = new User();
    $form = $this->createForm(UserForm::class, $user, [
        'form_role' => 'parent'
        ]);
    $form->handleRequest($request);
   

    if ($form->isSubmitted() && $form->isValid()) {
    $selectedChildren = $form->get('children')->getData();
    $user->setRoles(['ROLE_PARENT']);
    $user->setCreatedAt(new \DateTimeImmutable());
    $user->setIsActive(false);
    $user->setPassword('');

    foreach ($selectedChildren as $child) {
    $child->addUser($user);
    $em->persist($child);
}

    $activationToken = Uuid::v4()->toRfc4122();
    $user->setActivationToken($activationToken);

    $em->persist($user);
    $em->flush();

  try {
            $activationUrl = $this->generateUrl(
                'app_user_activate',
                ['token' => $activationToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $emailMessage = (new Email())
                ->from('no-reply@minus.fr')
                ->to($user->getEmail())
                ->subject('Activation de votre compte')
                ->html("<h2>Bienvenue !</h2>
                        <p>Bonjour {$user->getFirstName()} {$user->getLastName()},</p>
                        <p>Votre compte a été créé avec succès. Cliquez ci-dessous pour l'activer :</p>
                        <p><a href=\"$activationUrl\">Activer mon compte</a></p>
                        <p>Si le bouton ne fonctionne pas, copiez/collez ce lien : $activationUrl</p>");

            $mailer->send($emailMessage);
            $logger->info("Email d'activation envoyé à {$user->getEmail()}");
        } catch (\Exception $e) {
            $logger->error("Erreur lors de l'envoi du mail : " . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de l’envoi de l’email d’activation.');
         
        }

        $this->addFlash('success', 'Utilisateur créé et email envoyé');
        return $this->redirectToRoute('parent_create_form');
         }

        return $this->render('user/parent_create_form.html.twig', [
        'form' => $form->createView(),
        'user' => $user,
        ]);
    }
    #[Route('/admin/educ/create', name: 'educ_create_form', methods: ['GET', 'POST'])]
    public function showEducCreateForm(
    Request $request,
    EntityManagerInterface $em,
    MailerInterface $mailer,
    LoggerInterface $logger
    ): Response {
    $user = new User();
    $form = $this->createForm(UserForm::class, $user, [
        'form_role' => 'educ'
        ]);
    $form->handleRequest($request);
   

    if ($form->isSubmitted() && $form->isValid()) {
    $user->setRoles(['ROLE_EDUC']);
    $user->setCreatedAt(new \DateTimeImmutable());
    $user->setIsActive(false);
    $user->setPassword('');

    $activationToken = Uuid::v4()->toRfc4122();
    $user->setActivationToken($activationToken);

    $em->persist($user);
    $em->flush();

  try {
            $activationUrl = $this->generateUrl(
                'app_user_activate',
                ['token' => $activationToken],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            $emailMessage = (new Email())
                ->from('no-reply@minus.fr')
                ->to($user->getEmail())
                ->subject('Activation de votre compte')
                ->html("<h2>Bienvenue !</h2>
                        <p>Bonjour {$user->getFirstName()} {$user->getLastName()},</p>
                        <p>Votre compte a été créé avec succès. Cliquez ci-dessous pour l'activer :</p>
                        <p><a href=\"$activationUrl\">Activer mon compte</a></p>
                        <p>Si le bouton ne fonctionne pas, copiez/collez ce lien : $activationUrl</p>");

            $mailer->send($emailMessage);
            $logger->info("Email d'activation envoyé à {$user->getEmail()}");
        } catch (\Exception $e) {
            $logger->error("Erreur lors de l'envoi du mail : " . $e->getMessage());
            $this->addFlash('error', 'Erreur lors de l’envoi de l’email d’activation.');
            // Optionnel : rediriger ou continuer...
        }

        $this->addFlash('success', 'Utilisateur créé et email envoyé');
        
         }

        return $this->render('user\educ_create_form.html.twig', [
            'form' => $form->createView(),
        ]);
}
}