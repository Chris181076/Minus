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

#[Route('/child/presence')]
final class ChildPresenceController extends AbstractController
{
    #[Route(name: 'app_child_presence_index', methods: ['GET'])]
    public function index(ChildPresenceRepository $childPresenceRepository): Response
    {
        return $this->render('child_presence/index.html.twig', [
            'child_presences' => $childPresenceRepository->findAll(),
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
}
