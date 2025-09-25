<?php

namespace App\Controller;

use App\Entity\Child;
use App\Entity\ChildPresence;
use App\Entity\Semainier;
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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Repository\SemainierRepository;
use App\Service\WeekHelper;
use App\Entity\Group;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

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
    public function new(
    Request $request, 
    EntityManagerInterface $entityManager,
    SemainierRepository $semainierRepository,
    PlannedPresenceRepository $plannedPresenceRepo, 
    // WeekHelper $weekHelper 
    ): Response {
    $child = new Child();
    $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    // Récupérer le semainier de la semaine en cours
    $monday = (new \DateTimeImmutable('monday this week'))->setTime(0, 0);
    $semainier = $semainierRepository->findOneBy(['week_start_date' => $monday]);
    
    if (!$semainier) {
        // Créer le semainier s'il n'existe pas
        $semainier = new Semainier();
        $semainier->setWeekStartDate($monday);
        $entityManager->persist($semainier);
    }

    foreach ($days as $day) {
        $presence = new PlannedPresence();
        $presence->setWeekDay($day);
        $presence->setChild($child);
        $presence->setSemainier($semainier); // 🔥 Lier au semainier
        $presence->setCreatedAt(new \DateTime());
        
        $child->addPlannedPresence($presence);
    }

    $form = $this->createForm(ChildForm::class, $child, [
    'is_admin' => $this->isGranted('ROLE_ADMIN'),
    ]);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        // Tu peux gérer createdAt ici si besoin
        foreach ($child->getPlannedPresences() as $presence) {
            $presence->setCreatedAt(new \DateTime());
        }
        $plannedPresences = $child->getPlannedPresences()->toArray();
        $plannedPresenceRepo->assignPlannedPresencesToSemainier($semainier, $plannedPresences, $entityManager);

        $entityManager->persist($child);
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
    public function edit(Request $request, Child $child, EntityManagerInterface $entityManager, ChildRepository $childRepository, PlannedPresenceRepository $plannedPresenceRepo, SemainierRepository $semainierRepository): Response
    {
    if (!$this->isGranted('ROLE_ADMIN')) {
        return $this->render('bundles/TwigBundle/Exception/error403.html.twig', [], new Response('', 403));
    }
    $plannedPresences = $child->getPlannedPresences();
    $monday = (new \DateTimeImmutable('monday this week'))->setTime(0, 0);
    $semainier = $semainierRepository->findOneBy(['week_start_date' => $monday]);
    if (!$semainier) {
        $semainier = new Semainier();
        $semainier->setWeekStartDate($monday);
        $entityManager->persist($semainier);
    }

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
        $plannedPresences = $child->getPlannedPresences()->toArray();
        $plannedPresenceRepo->assignPlannedPresencesToSemainier($semainier, $plannedPresences, $entityManager);

        $entityManager->persist($child);
        $entityManager->flush();

        if ($this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('app_child_index', [], Response::HTTP_SEE_OTHER);
        }
        return $this->redirectToRoute('app_child_show', ['id' => $child->getId()], Response::HTTP_SEE_OTHER);
    }
     $children = $childRepository->findAll();
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
    $user = $this->getUser();
    $children = $childRepository->findByUser($user);

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