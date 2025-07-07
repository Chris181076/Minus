<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;
use App\Repository\ChildRepository;
use App\Entity\Child;
use App\Form\PlannedPresenceForm;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\PlannedPresence;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use App\Form\ChildPresenceForm;
use App\Entity\ChildPresence;
use App\Form\ChildPlanningForm;
use App\Entity\Semainier;
use App\Repository\PlannedPresenceRepository;
use App\Form\ChildForm;

final class ParentController extends AbstractController
{
    #[Route('/parent/dashboard', name: 'parent_dashboard')]
    public function dashboard(Security $security, ChildRepository $childRepository): Response
    {
        $user=$security->getUser();
        $firstName = $user->getFirstName();
        $children = $childRepository->findByUser($user);
        return $this->render('parent/index.html.twig', [
            'controller_name' => 'ParentController',
            'firstName' => $firstName,
            'children' => $children,
        ]);
    }
  #[Route('/parent/planningMinus/{id}', name: 'planningMinus')]
public function showChildPlanning(Child $child, EntityManagerInterface $em, ChildRepository $childRepo, PlannedPresenceRepository $plannedPresenceRepository, Semainier $semainier): Response
{
    $user = $child->getUser();
    $children = $childRepo->findByUser($user);
    if (!$child->getUser()) {
    $child->setUser(new User());
    }
    $form = $this->createForm(ChildForm::class, $child);
    // 1. Vérifie si l'enfant a déjà des présences planifiées
    $plannedPresences = $plannedPresenceRepository->findByChildOrderedByWeekday($child);

    $plannedPresenceRepository->assignPlannedPresencesToSemainier($semainier, $plannedPresences, $em);

    // 2. Si aucune présence planifiée, crée-les pour les jours souhaités
    if (count($plannedPresences) === 0) {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        foreach ($days as $day) {
            $presence = new PlannedPresence();
            $presence->setWeekDay($day);
            $presence->setChild($child);
            $em->persist($presence);
            $child->addPlannedPresence($presence);
        }
        $em->flush();

        // Recharge après insertion
        $plannedPresences = $em->getRepository(PlannedPresence::class)
            ->findBy(['child' => $child], ['week_day' => 'ASC']);
    }

    // 3. Prépare un tableau de jours (utile si tu veux l'afficher)
    $weekDays = [];
    foreach ($plannedPresences as $presence) {
        $weekDays[] = $presence->getWeekDay();
    }

    // 4. Choisir le template en fonction du rôle
    $template = $this->isGranted('ROLE_PARENT') ? 'base_parent.html.twig' : 'base_admin.html.twig';

    return $this->render('parent/planningMinus.html.twig', [
        'base_template' => $template,
        'child' => $child,
        'planned_presences' => $plannedPresences,
        'user' => $user,
        'week_days' => $weekDays,
        'children' => $children,
    ]);
}

}


 