<?php

namespace App\Controller;

use App\Entity\Journal;
use App\Form\JournalForm;
use App\Repository\JournalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\ChildPresence;
use App\Repository\ChildPresenceRepository;
use App\Entity\Child;

#[Route('/journal')]
final class JournalController extends AbstractController
{
 #[Route('/new/{presenceId}', name: 'app_journal_new', methods: ['GET', 'POST'])]
public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    ChildPresenceRepository $presenceRepo,
    int $presenceId
): Response {
    $presence = $presenceRepo->find($presenceId);

    if (!$presence) {
        throw $this->createNotFoundException('Présence non trouvée pour ID ' . $presenceId);
    }
    $child = $presence->getChild();
    $journal = new Journal();
    $journal->setDate($presence->getArrivalTime()->setTime(0, 0));
    $journal->setChild($presence->getChild());

    $form = $this->createForm(JournalForm::class, $journal);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->persist($journal);
        $entityManager->flush();

        return $this->redirectToRoute('app_journal_index');
    }

    return $this->render('journal/new.html.twig', [
        'journal' => $journal,
        'form' => $form,
        'child' => $child,
    ]);
}



    #[Route(name: 'app_journal_index', methods: ['GET'])]
    public function index(JournalRepository $journalRepository): Response
    {
        return $this->render('journal/index.html.twig', [
            'journals' => $journalRepository->findAll(),
        ]);
    }

    /*#[Route('/{id}', name: 'app_journal_single', methods: ['GET'])]
    public function show(Journal $journal): Response
    {
        return $this->render('journal/show.html.twig', [
            'journal' => $journal,
        ]);
    }*/

    #[Route('/{id}/edit', name: 'app_journal_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Journal $journal, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(JournalForm::class, $journal);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_journal_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('journal/edit.html.twig', [
            'journal' => $journal,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_journal_delete', methods: ['POST'])]
    public function delete(Request $request, Journal $journal, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$journal->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($journal);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_journal_index', [], Response::HTTP_SEE_OTHER);
    }
}
