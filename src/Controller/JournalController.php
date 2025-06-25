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
use App\Service\JournalParser;
use App\Entity\JournalEntry;

#[Route('/journal')]
final class JournalController extends AbstractController
{
 #[Route('/new/{presenceId}', name: 'app_journal_new', methods: ['GET', 'POST'])]
public function new(
    Request $request,
    EntityManagerInterface $entityManager,
    ChildPresenceRepository $presenceRepo,
    JournalRepository $journalRepo,
    int $presenceId
): Response {
    $presence = $presenceRepo->find($presenceId);

    if (!$presence) {
        throw $this->createNotFoundException('Présence non trouvée pour ID ' . $presenceId);
    }

    $date = $presence->getArrivalTime()->setTime(0, 0);
    $child = $presence->getChild();

    // Rechercher un journal existant
    $journal = $journalRepo->findOneBy([
        'child' => $child,
        'date' => $date,
    ]);

    // Sinon, on en crée un
    if (!$journal) {
        $journal = new Journal();
        $journal->setDate($date);
        $journal->setChild($child);
    }

    $form = $this->createForm(JournalForm::class, $journal);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        foreach ($journal->getEntries() as $entry) {
        $entry->setJournal($journal);
        }

        $entityManager->persist($journal);
        $entityManager->flush();

        return $this->redirectToRoute('app_admin_dashboard_journal');
    }

    return $this->render('journal/new.html.twig', [
        'form' => $form->createView(),
        'child' => $child,
        'journal'=> $journal,
    ]);
}




    #[Route(name: 'app_journal_index', methods: ['GET'])]
    public function index(JournalRepository $journalRepository, ChildPresenceRepository $presenceRepo): Response
    {
        $presence = $presenceRepo->findOneBy([], ['arrivalTime' => 'DESC']);
        return $this->render('journal/index.html.twig', [
            'journals' => $journalRepository->findAll(),
            'presence' => $presence,
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
