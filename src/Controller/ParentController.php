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
    $user = $security->getUser();
    $children = $childRepository->findByUser($user);
    $child = $children[0] ?? null;
    $firstName = $user->getFirstName();

    return $this->render('parent/index.html.twig', [
        'children' => $children,
        'child' => $child,
        'base_template' => 'base_parent.html.twig',
        'firstName' => $firstName,
        'user' => $user,
    ]);
}

  #[Route('/parent/planningMinus/{id}', name: 'planningMinus')]
public function showChildPlanning(Child $child, EntityManagerInterface $em, ChildRepository $childRepo, PlannedPresenceRepository $plannedPresenceRepository): Response
{
    
    $user = $this->getUser();
    $children = $childRepo->findByUser($user);
    
    
    $form = $this->createForm(ChildForm::class, $child);

   
    // 1. Vérifie si l'enfant a déjà des présences planifiées
    $plannedPresences = $plannedPresenceRepository->findByChildOrderedByWeekday($child);

    // 3. Prépare un tableau de jours (utile si tu veux l'afficher)
    $weekDays = [];
    foreach ($plannedPresences as $presence) {
        $weekDays[] = $presence->getWeekDay();
    }

    return $this->render('parent/planningMinus.html.twig', [
        'child' => $child,
        'planned_presences' => $plannedPresences,
        'users' => $user,
        'week_days' => $weekDays,
        'children' => $children,
    ]);
}

}


 