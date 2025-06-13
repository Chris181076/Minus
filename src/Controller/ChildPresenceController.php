<?php

namespace App\Controller;

use App\Entity\ChildPresence;
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
        if ($this->isCsrfTokenValid('delete'.$childPresence->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($childPresence);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_child_presence_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/{day?}', name: 'app_daily_planning', methods: ['GET'])]
    public function dailyPlanning($day, ChildRepository $childRepository, ChildPresenceRepository $presenceRepo): Response
{
    $now = new \DateTimeImmutable('now');
    $monday = $now->modify('monday this week');

    $days = [];
    for ($i = 0; $i < 5; $i++) {
        $days[] = $monday->add(new \DateInterval("P{$i}D"));
    }

    if (!$day) {
        $day = $days[0]->format('Y-m-d');
    }

    $dateObj = new \DateTimeImmutable($day);
    $children = $childRepository->findAll();

    // Map des présences par enfant ID
    $presences = $presenceRepo->findBy(['day' => $dateObj]);
    $presenceMap = [];
    foreach ($presences as $presence) {
        $presenceMap[$presence->getChild()->getId()] = $presence;
    }

    return $this->render('ChildPresence/index_admin.html.twig', [
        'day' => $day,
        'days' => $days,
        'children' => $children,
        'presenceMap' => $presenceMap,
    ]);
    }

    
#[Route('/child/presence/mark-departure/{id}', name: 'app_mark_departure', methods: ['POST'])]
public function markDeparture(ChildPresence $childPresence, EntityManagerInterface $em): Response
{
    date_default_timezone_set('Europe/Paris');
    $now = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', date('Y-m-d H:i:s'));
    $childPresence->setDepartureTime($now);
    $em->flush();

    return $this->json([
        'success' => true,
        'departureTime' => $childPresence->getDepartureTime()->format('H:i:s'),
    ]);
}
    
}
