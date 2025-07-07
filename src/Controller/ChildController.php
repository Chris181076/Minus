<?php

namespace App\Controller;

use App\Entity\Child;
use App\Entity\ChildPresence;
use App\Form\ChildForm;
use App\Repository\ChildRepository;
use App\Repository\IconRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Entity\PlannedPresence;
use Symfony\Component\Security\Core\Security;
use App\Repository\ChildPresenceRepository;
use App\Repository\PlannedPresenceRepository;



#[Route('/child')]
final class ChildController extends AbstractController
{
    #[Route(name: 'app_child_index', methods: ['GET'])]
    public function index(ChildRepository $childRepository): Response
    {
        return $this->render('child/index.html.twig', [
            'children' => $childRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_child_new', methods: ['GET', 'POST'])]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $child = new Child();
    $child->setUser($this->getUser());

    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
    foreach ($days as $day) {
        $presence = new PlannedPresence();
        $presence->setWeekDay($day); 
        $presence->setChild($child);
        $child->addPlannedPresence($presence);
    }

    $form = $this->createForm(ChildForm::class, $child);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Tu peux gérer createdAt ici si besoin
        foreach ($child->getPlannedPresences() as $presence) {
            $presence->setCreatedAt(new \DateTime());
        }

        $entityManager->persist($child);
        $entityManager->flush();

        return $this->redirectToRoute('app_child_index');
    }

    return $this->render('child/new.html.twig', [
        'child' => $child,
        'form' => $form->createView(),
    ]);
}

    #[Route('/{id}/edit', name: 'app_child_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, Child $child, EntityManagerInterface $entityManager, ChildRepository $childRepository): Response
{
    $plannedPresences = $child->getPlannedPresences();

    // Si pas de plannedPresences, créer des présences par défaut (lundi à vendredi)
    if ($plannedPresences->isEmpty()) {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        foreach ($days as $day) {
            $presence = new PlannedPresence();
            $presence->setWeekDay($day);
            $presence->setArrivalTime(null);
            $presence->setDepartureTime(null);
            $presence->setChild($child);
            $presence->setCreatedAt(new \DateTimeImmutable());
            $child->addPlannedPresence($presence);
        }
    }

    $form = $this->createForm(ChildForm::class, $child, [
    'is_admin' => $this->isGranted('ROLE_ADMIN'),
    ]);
    $form->handleRequest($request);

    $template = $this->isGranted('ROLE_PARENT') ? 'base_parent.html.twig' : 'base_admin.html.twig';

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_child_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->redirectToRoute('app_child_show', ['id' => $child->getId()], Response::HTTP_SEE_OTHER);
    }
     $children = $childRepository->findBy(['user' => $this->getUser()]);
    return $this->render('child/edit.html.twig', [
        'child' => $child,
        'form' => $form->createView(),
        'base_template' => $template,  
        'children' => $children,      
    ]);
}


    #[Route('/{id}', name: 'app_child_delete', methods: ['POST'])]
    public function delete(Request $request, Child $child, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$child->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($child);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_child_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/show/{id}', name: 'app_child_show', methods: ['GET', 'POST'])]
    public function show(Request $request, Child $child, EntityManagerInterface $entityManager, ChildRepository $childRepository, PlannedPresenceRepository $plannedPresenceRepository): Response
    {
    $user = $this->getUser(); // Pas besoin d’injecter Security
    $children = $childRepository->findBy(['user' => $user]);

    $template = $this->isGranted('ROLE_PARENT') ? 'base_parent.html.twig' : 'base_admin.html.twig';

    // Début de la semaine (lundi)
    $plannedPresences = $plannedPresenceRepository->findByChildOrderedByWeekday($child);

    return $this->render('child/show.html.twig', [
        'child' => $child,
        'children' => $children,
        'base_template' => $template,
        'planned_presences' => $plannedPresences,
        'user' => $user,
    ]);
    }


}
