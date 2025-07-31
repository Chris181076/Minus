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
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

#[Route('/child/presence')]
final class ChildPresenceController extends AbstractController
{
    #[Route(name: 'app_child_presence_index', methods: ['GET', 'POST'])]
    public function index(ChildPresenceRepository $childPresenceRepository, ChildRepository $childRepository, CsrfTokenManagerInterface $csrfTokenManager): Response
{
   $now = new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris'));
    $monday = $now->modify('monday this week');

    // Liste des jours de la semaine (lundi à vendredi)
    $days = [];
    for ($i = 0; $i < 5; $i++) {
        $days[] = $monday->add(new \DateInterval("P{$i}D"));
    }

    // Jour actif par défaut = lundi
    $day = $days[0]->format('Y-m-d');
    $date = new \DateTimeImmutable($day, new \DateTimeZone('Europe/Paris'));
    $date = $date->setTime(0, 0); // Très important : on enlève les heures/minutes pour matcher correctement

    $children = $childRepository->findAll();
    $presences = $childPresenceRepository->findBy(['day' => $date]);
    $presenceMap = [];
        foreach ($presences as $presence) {
            $childId = $presence->getChild()->getId();
            $presenceMap[$childId] = $presence;
        }
        

$date = (new \DateTimeImmutable())->setTime(0, 0);

$presences = $childPresenceRepository->findByDay($date);

$csrfTokens = [];

foreach ($presences as $presence) {
    if (null === $presence->getId()) {
        continue; 
    }
    
    $id = $presence->getId();
    $csrfTokens[$id] = $csrfTokenManager->getToken('delete' . $id)->getValue();
}
   


    $template = $this->isGranted('ROLE_ADMIN')
        ? 'child_presence/index.html.twig'
        : 'child_presence/index.html.twig';

    return $this->render($template, [
        'child_presences' => $presences,
        'days' => $days,
        'day' => $day,
        'children' => $children,
        'no_sidebar' => true,
        'presenceMap' => $presenceMap,  
        'presences'  => $presences,  
        'presence' => $presence,
        'csrfTokens' => $csrfTokens, 
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


#[Route('/{id}/delete', name: 'app_child_presence_delete', requirements: ['id' => '\d+'], methods: ['POST'])]
public function delete(
    Request $request,
    EntityManagerInterface $entityManager,
    ChildPresenceRepository $childPresenceRepository,
    CsrfTokenManagerInterface $csrfTokenManager,
    int $id
): Response {
    $presence = $childPresenceRepository->find($id);

    if (!$presence) {
        return $this->json([
            'success' => false,
            'message' => 'Présence introuvable'
        ], Response::HTTP_NOT_FOUND);
    }

    // Vérification du token CSRF global
    if (!$this->isCsrfTokenValid('delete', $request->request->get('_token'))) {
        return $this->json([
            'success' => false,
            'message' => 'Token CSRF invalide'
        ], Response::HTTP_FORBIDDEN);
    }

    $entityManager->remove($presence);
    $entityManager->flush();

    return $this->json([
        'success' => true,
        'message' => 'Présence supprimée',
        'presenceId' => $id
    ]);
}






#[Route('/mark-arrival/{id}', name: 'app_mark_arrival', methods: ['POST'])]
public function markArrival(
    Request $request,
    Child $child,
    EntityManagerInterface $em,
    SemainierRepository $semainierRepo,
    JournalRepository $journalRepo,
    ChildPresenceRepository $presenceRepo,
    CsrfTokenManagerInterface $csrfTokenManager,
): JsonResponse {
    try {
        $timezone = new \DateTimeZone('Europe/Paris');
        // Si une heure est envoyée depuis le JS, on l’utilise
        $data = json_decode($request->getContent(), true);
        $arrivalTimeStr = $data['arrivalTime'] ?? null;
     
        if ($arrivalTimeStr ) {
            $now = new \DateTimeImmutable($arrivalTimeStr, $timezone);
        } else {
            $now = new \DateTimeImmutable('now', $timezone);
        }

        $todayDateStr = $now->format('Y-m-d');
        $today = new \DateTimeImmutable($todayDateStr, $timezone);


        // Chercher une présence existante pour éviter les doublons
        $existingPresence = $presenceRepo->findOneBy([
            'child' => $child,
            'day' => $today
        ]);

        if ($existingPresence) {
            return $this->json([
                'success' => true,
                'message' => 'Déjà marqué présent.',
                'presenceId' => $existingPresence->getId(),
                'arrivalTime' => $existingPresence->getArrivalTime()?->format('c'),
            ]);
        }

        // Création du journal si nécessaire
        $journal = $journalRepo->findOneByChildAndDate($child, $today);
        if (!$journal) {
            $journal = new \App\Entity\Journal();
            $journal->setChild($child);
            $journal->setDate($today);
            $em->persist($journal);
        }

        // Création de la présence
        $presence = new \App\Entity\ChildPresence();
        $presence
            ->setChild($child)
            ->setDay($today)
            ->setArrivalTime($now)
            ->setPresent(true);

        $em->persist($presence);
        $em->flush();

        

        return $this->json([
            'success' => true,
            'arrivalTime' => $presence->getArrivalTime()->format('c'),
            'presenceId' => $presence->getId(),
            'journalId' => $journal->getId(),
            'csrf_tokens' => [],
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

        return $this->render('child_presence/index.html.twig', [
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
#[Route('/update-time/{id}', name: 'app_child_presence_update_time', methods: ['POST'])]
public function updateTime(Request $request, int $id, EntityManagerInterface $em): JsonResponse
{
    $presence = $em->getRepository(ChildPresence::class)->find($id);
    if (!$presence) {
        return $this->json(['success' => false, 'message' => 'Présence introuvable'], 404);
    }

    $data = json_decode($request->getContent(), true);
    $timeStr = $data['time'] ?? null;
    $type = $data['type'] ?? null;

    if (!$type || !$timeStr || !preg_match('/^\d{2}:\d{2}$/', $timeStr)) {
        return $this->json(['success' => false, 'message' => 'Format invalide']);
    }

    try {
        [$h, $m] = explode(':', $timeStr);
        $dt = (new \DateTimeImmutable('now', new \DateTimeZone('Europe/Paris')))
            ->setTime((int)$h, (int)$m);

        if ($type === 'arrival') {
            $presence->setArrivalTime($dt);
        } elseif ($type === 'departure') {
            $presence->setDepartureTime($dt);
        } else {
            return $this->json(['success' => false, 'message' => 'Type inconnu']);
        }

        $em->flush();

        return $this->json(['success' => true]);
    } catch (\Exception $e) {
        return $this->json(['success' => false, 'message' => $e->getMessage()]);
    }
}





}
