<?php

namespace App\Controller;

use App\Entity\Semainier;
use App\Form\SemainierForm;
use App\Repository\SemainierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Child;
use App\Repository\ChildRepository;
use App\Entity\ChildPresence;
use App\Repository\ChildPresenceRepository;
use App\Service\WeekHelper;
use App\Repository\PlannedPresenceRepository;
use App\Entity\PlannedPresence;
use App\Service\SemainierManager;

#[Route('/semainier')]
final class SemainierController extends AbstractController
{
#[Route(name: 'app_semainier_index', methods: ['GET'])]
public function index(SemainierRepository $semainierRepository, WeekHelper $weekHelper, ChildRepository $childRepository): Response
{
    [$start, $end] = $weekHelper->getWeekStartAndEnd();
    $children = $childRepository->findAll();
    // On récupère les semainiers de la semaine avec leurs plannedPresences
    $semainiers = $semainierRepository->createQueryBuilder('s')
        ->leftJoin('s.plannedPresences', 'pp') // jointure pour charger les données
        ->addSelect('pp')
        ->leftJoin('pp.child', 'c') 
        ->addSelect('c')
        ->where('pp.arrival_time BETWEEN :start AND :end OR pp.departure_time BETWEEN :start AND :end')
        ->setParameter('start', $start)
        ->setParameter('end', $end)
        ->orderBy('s.week_start_date', 'DESC')
        ->orderBy('s.week_start_date', 'DESC')
        ->distinct()
        ->getQuery()
        ->getResult();
        

    return $this->render('semainier/index.html.twig', [
        'semainiers' => $semainiers,
        'week_days' => $weekHelper->getWeekDays(),
        'week_start' => $start,
        'week_end' => $end,
        'weekDays' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
        'children' => $children,
    ]);
}


    #[Route('/new', name: 'app_semainier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $semainier = new Semainier();
        $form = $this->createForm(SemainierForm::class, $semainier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($semainier);
            $entityManager->flush();

            return $this->redirectToRoute('app_semainier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('semainier/new.html.twig', [
            'semainier' => $semainier,
            'form' => $form,
        ]);
    }

#[Route('/show/{id}', name: 'app_semainier_show', methods: ['GET'])]
public function show(
    Semainier $semainier,
    ChildRepository $childRepository,
    ChildPresenceRepository $childPresenceRepository,
    EntityManagerInterface $entityManager,
    WeekHelper $weekHelper,
    SemainierManager $semainierManager, 
    PlannedPresenceRepository $plannedPresenceRepo,
    SemainierRepository $semainierRepository,
): Response {
    // Ici, $semainier est automatiquement injecté par Symfony grâce à l'id dans l'URL
    $startOfWeek = $weekHelper->getStartOfWeek($semainier->getWeekStartDate());
    $endOfWeek = $weekHelper->getEndOfWeek($semainier->getWeekStartDate());

    $children = $childRepository->findAll();
    $weekDays = $weekHelper->getWeekDays();
    $lastSemainier = $semainierRepository->lastSemainier();
    $semainiers = $semainierRepository->currentMonthSemainier();

    foreach ($children as $child) {
        $presences = $plannedPresenceRepo->findByChildAndWeek($child, $startOfWeek, $endOfWeek);
        
        foreach ($presences as $presence) {
            $days[] = $presence->getWeekDay()->format('l');
        }

            foreach ($presences as $presence) {
        // Accès aux données si besoin
        $day = $presence->getWeekDay()->format('l');
        $arrival = $presence->getArrivalTime();
        $departure = $presence->getDepartureTime();
        }
    }
    foreach ($semainier as $sem) {
       echo $sem->getWeekStartDate()->format('d/m/Y');
    }
  


    return $this->render('semainier/show.html.twig', [
        'semainier' => $semainier,
        'lastSemainier' => $lastSemainier,
        'children' => $children,
        'weekDays' => $weekDays,
        'startOfWeek' => $startOfWeek,
        'semainiers' => $semainiers,
    ]);
}




         

    #[Route('/{id}/edit', name: 'app_semainier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Semainier $semainier, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SemainierForm::class, $semainier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_semainier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('semainier/edit.html.twig', [
            'semainier' => $semainier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_semainier_delete', methods: ['POST'])]
    public function delete(Request $request, Semainier $semainier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$semainier->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($semainier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_semainier_index', [], Response::HTTP_SEE_OTHER);
    }
    
}
