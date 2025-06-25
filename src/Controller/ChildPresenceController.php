<?php

namespace App\Controller;
use App\Entity\Child;
use App\Entity\ChildPresence;
use App\Entity\Semainier;
use App\Form\ChildPresenceForm;
use App\Repository\ChildPresenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ChildRepository;
use \DateInterval;
use \DateTimeImmutable;
use DateTimeZone;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\SemainierRepository;
use App\Repository\JournalRepository;
use App\Entity\Journal;

#[Route('/child/presence')]
final class ChildPresenceController extends AbstractController
{
    #[Route(name: 'app_child_presence_index', methods: ['GET'])]
    public function index(ChildPresenceRepository $childPresenceRepository, ChildRepository $childRepository): Response
{
    $now = new \DateTimeImmutable('now');
    $monday = $now->modify('monday this week');
    $children = $childRepository->findAll();
    $days = [];

    for ($i = 0; $i < 5; $i++) {
        $days[] = $monday->add(new \DateInterval("P{$i}D"));
    }

    // On choisit le lundi comme jour actif par défaut
    $day = $days[0]->format('Y-m-d');

    $template = $this->isGranted('ROLE_ADMIN')
        ? 'child_presence/index_admin.html.twig'
        : 'child_presence/index.html.twig';

    return $this->render($template, [
        'child_presences' => $childPresenceRepository->findAll(),
        'days' => $days,
        'day' => $day,
        'children' => $children,
    ]);
}


    #[Route('/new', name: 'app_child_presence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $childPresence = new ChildPresence();
        $form = $this->createForm(ChildPresenceForm::class, $childPresence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($childPresence);
            $entityManager->flush();

            return $this->redirectToRoute('app_child_presence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('child_presence/new.html.twig', [
            'child_presence' => $childPresence,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_child_presence_show', methods: ['GET'])]
    public function show(ChildPresence $childPresence): Response
    {
        return $this->render('child_presence/show.html.twig', [
            'child_presence' => $childPresence,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_child_presence_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ChildPresence $childPresence, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ChildPresenceForm::class, $childPresence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_child_presence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('child_presence/edit.html.twig', [
            'child_presence' => $childPresence,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_child_presence_delete', methods: ['POST'])]
public function delete(Request $request, ChildPresence $childPresence, EntityManagerInterface $entityManager): Response
{
    // Vérifier le token CSRF
    $token = $request->request->get('_token');
    if (!$this->isCsrfTokenValid('delete'.$childPresence->getId(), $token)) {
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => false, 
                'message' => 'Token CSRF invalide'
            ], 403);
        }
        throw $this->createAccessDeniedException('Token CSRF invalide');
    }

    try {
        $entityManager->remove($childPresence);
        $entityManager->flush();

        // Si c'est une requête AJAX, retourner JSON
        if ($request->isXmlHttpRequest()) {
            return $this->json(['success' => true]);
        }

        return $this->redirectToRoute('app_child_presence_index', [], Response::HTTP_SEE_OTHER);
        
    } catch (\Exception $e) {
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => false, 
                'message' => 'Erreur lors de la suppression: ' . $e->getMessage()
            ], 500);
        }
        throw $e;
    }
}



#[Route('/mark-arrival/{id}', name: 'app_mark_arrival', methods: ['POST'])]
public function markArrival(
    Child $child,
    EntityManagerInterface $em,
    SemainierRepository $semainierRepo,
    JournalRepository $journalRepo,
): JsonResponse {
    try {
        $timezone = new \DateTimeZone('Europe/Paris');
        $now = new \DateTimeImmutable('now', $timezone);
        $today = $now->setTime(0, 0);

        // Recherche ou création du journal du jour pour cet enfant
        $existingJournal = $journalRepo->findOneByChildAndDate($child, $today);
        if (!$existingJournal) {
            $journal = new \App\Entity\Journal();
            $journal->setChild($child);
            $journal->setDate($today);
            $em->persist($journal);
        } else {
    $journal = $existingJournal;
}

        // Recherche ou création du semainier pour la semaine en cours
    
        $monday = $now->modify('monday this week')->setTime(0, 0);
        $semainier = $semainierRepo->findOneBy(['week_start_date' => $monday]);
        if (!$semainier) {
            $semainier = new \App\Entity\Semainier();
            $semainier->setWeekStartDate($monday);
            $em->persist($semainier);
        }

        $presence = new \App\Entity\ChildPresence();
        $presence
            ->setChild($child)
            ->setDay($today)
            ->setArrivalTime($now)
            ->setSemainier($semainier)
            ->setPresent(true);

        $em->persist($presence);
        $em->flush();

        return $this->json([
            'success' => true,
            'arrivalTime' => $presence->getArrivalTime()->format('c'),
            'presenceId' => $presence->getId(),
            'journalId' => $journal ? $journal->getId() : null
        ]);
    } catch (\Exception $e) {
        return $this->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}

    #[Route('/mark-departure/{id}', name: 'app_mark_departure', methods: ['POST'])]
    public function markDeparture(
    int $id,
    ChildPresenceRepository $presenceRepo,
    EntityManagerInterface $em
    ): JsonResponse {
    $childPresence = $presenceRepo->find($id);

    if (!$childPresence) {
        return $this->json([
            'success' => false,
            'message' => 'Présence non trouvée'
        ], 404);
    }

    if ($childPresence->getDepartureTime() !== null) {
        return $this->json([
            'success' => false,
            'message' => 'Le départ a déjà été marqué'
        ], 400);
    }

    $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
    $childPresence->setDepartureTime($now);

    $em->flush();

    return $this->json([
        'success' => true,
        'departureTime' => $childPresence->getDepartureTime()->format('c')
    ]);
}


    #[Route('/{day?}', name: 'app_daily_planning', methods: ['GET'])]
    public function dailyPlanning(
        ?string $day=null): Response {
        $now = new DateTimeImmutable('now', new DateTimeZone('Europe/Paris'));
        $monday = $now->modify('monday this week');
        
        // Generate week days
        $days = [];
        for ($i = 0; $i < 5; $i++) {
            $days[] = $monday->add(new \DateInterval("P{$i}D"));
        }

        // Use first day if none specified
        if (!$day) {
            $day = $days[0]->format('Y-m-d');
        }

        $dateObj = new \DateTimeImmutable($day, new \DateTimeZone('Europe/Paris'));
        $dateObj = $dateObj->setTime(0, 0, 0);
        
        $children = $childRepository->findAll();
        // Map presences by child ID
        $presences = $presenceRepo->findBy(['day' => $dateObj]);
        $presenceMap = [];
        foreach ($presences as $presence) {
            $presenceMap[$presence->getChild()->getId()] = $presence;
        }

        return $this->render('child_presence/index_admin.html.twig', [
            'day' => $day,
            'days' => $days,
            'children' => $children,
            'presenceMap' => $presenceMap,
        ]);
    }
    #[Route('/sync/{day}', name: 'app_child_presence_sync', methods: ['GET'])]
public function syncPresences(
    string $day,
    ChildPresenceRepository $presenceRepo,
    ChildRepository $childRepo
): JsonResponse {
    try {
        $dateObj = new \DateTimeImmutable($day, new \DateTimeZone('Europe/Paris'));
        $dateObj = $dateObj->setTime(0, 0, 0);
        
        // Récupérer toutes les présences pour ce jour
        $presences = $presenceRepo->findBy(['day' => $dateObj]);
        
        // Construire le tableau de présences pour le localStorage
        $presenceData = [];
        foreach ($presences as $presence) {
            $childId = $presence->getChild()->getId();
            $presenceData[$childId] = [
                'presenceId' => $presence->getId(),
                'childId' => $childId,
                'arrivalTime' => $presence->getArrivalTime() ? $presence->getArrivalTime()->format('c') : null,
                'departureTime' => $presence->getDepartureTime() ? $presence->getDepartureTime()->format('c') : null,
            ];
        }
        
        return $this->json([
            'success' => true,
            'presences' => $presenceData
        ]);
    } catch (\Exception $e) {
        return $this->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 500);
    }
}



}