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

#[Route('/semainier')]
final class SemainierController extends AbstractController
{
    #[Route(name: 'app_semainier_index', methods: ['GET'])]
    public function index(SemainierRepository $semainierRepository): Response
    {
        return $this->render('semainier/index.html.twig', [
            'semainiers' => $semainierRepository->findAll(),
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

    #[Route('/{id}', name: 'app_semainier_show', methods: ['GET'])]
    public function show(Semainier $semainier): Response
    {
        return $this->render('semainier/show.html.twig', [
            'semainier' => $semainier,
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
