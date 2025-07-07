<?php

namespace App\Controller;

use App\Entity\PlannedPresence;
use App\Form\PlannedPresenceForm;
use App\Repository\PlannedPresenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/planned/presence')]
final class PlannedPresenceController extends AbstractController
{
    #[Route(name: 'app_planned_presence_index', methods: ['GET'])]
    public function index(PlannedPresenceRepository $plannedPresenceRepository): Response
    {
        return $this->render('planned_presence/index.html.twig', [
            'planned_presences' => $plannedPresenceRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_planned_presence_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $plannedPresence = new PlannedPresence();
        $form = $this->createForm(PlannedPresenceForm::class, $plannedPresence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $planning = new PlannedPresence();
            $planning->setChild($child);
            $planning->setDay('Monday'); // par exemple
            $planning->setStartHour(new \DateTime('08:00'));
            $planning->setEndHour(new \DateTime('16:00'));
            $entityManager->persist($plannedPresence);
            $entityManager->persist($child);
            $entityManager->flush();

            return $this->redirectToRoute('app_planned_presence_index', ['id' => $child->getId()]);
        }

        return $this->render('planned_presence/new.html.twig', [
            'planned_presence' => $plannedPresence,
            'form' => $form,
            'child' => $child,
        ]);
    }
    #[Route('/{id}', name: 'app_planned_presence_show', methods: ['GET', 'POST'])]
    public function show(int $id, PlannedPresenceRepository $repo): Response
{
    $plannedPresence = $repo->find($id);
    if (!$plannedPresence) {
        throw $this->createNotFoundException('PlannedPresence not found');
    }
        return $this->render('planned_presence/show.html.twig', [
            'planned_presence' => $plannedPresence,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_planned_presence_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, PlannedPresence $plannedPresence, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PlannedPresenceForm::class, $plannedPresence);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_planned_presence_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('planned_presence/edit.html.twig', [
            'planned_presence' => $plannedPresence,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_planned_presence_delete', methods: ['POST'])]
    public function delete(Request $request, PlannedPresence $plannedPresence, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$plannedPresence->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($plannedPresence);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_planned_presence_index', [], Response::HTTP_SEE_OTHER);
    }
}
