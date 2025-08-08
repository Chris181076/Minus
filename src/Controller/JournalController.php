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
use App\Repository\ChildRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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
        throw $this->createNotFoundException('Présence introuvable.');
    }

    $child = $presence->getChild();

    // On prend uniquement la date (heure mise à 00:00)
    $date = $presence->getArrivalTime()->setTime(0, 0);
    if (!$date) {
        throw new \LogicException('La présence n\'a pas d\'heure d\'arrivée.');
    }
    $date = $date->setTime(0, 0);
    
    // On tente de récupérer un journal existant
    $journal = $journalRepo->findOneBy([
        'child' => $child,
        'date' => $date,
    ]);

    // S'il n'existe pas, on le crée
    if (!$journal) {
        $journal = new Journal();
        $journal->setChild($child);
        $journal->setDate($date);
        $entityManager->persist($journal);
        $entityManager->flush();
    }

    // Créer un journal temporaire UNIQUEMENT pour le formulaire avec une seule entrée vide
    $formJournal = new Journal();
    $formJournal->setChild($child);
    $formJournal->setDate($date);
    $formJournal->addEntry(new JournalEntry()); // Une seule ligne vide

    $form = $this->createForm(JournalForm::class, $formJournal);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $hasNewEntry = false;
        
        // Traiter l'entrée soumise (il ne devrait y en avoir qu'une)
        foreach ($formJournal->getEntries() as $entryData) {
            // Si l'entrée n'est pas vide
            if (!empty($entryData->getHeure()) || 
                !empty($entryData->getAction()) || 
                !empty($entryData->getDescription()) || 
                !empty($entryData->getNote())) {
                
                // Créer une nouvelle entrée et l'ajouter au vrai journal
                $newEntry = new JournalEntry();
                $newEntry->setHeure($entryData->getHeure());
                $newEntry->setAction($entryData->getAction());
                $newEntry->setDescription($entryData->getDescription());
                $newEntry->setNote($entryData->getNote());
                $newEntry->setJournal($journal);
                
                $journal->addEntry($newEntry);
                $entityManager->persist($newEntry);
                $hasNewEntry = true;
            }
        }

        if ($hasNewEntry) {
            $entityManager->flush();
            $this->addFlash('success', 'Entrée ajoutée avec succès !');
            
            // Rediriger pour éviter la resoumission et rafraîchir les données
            return $this->redirectToRoute('app_journal_new', ['presenceId' => $presenceId]);
        }
    }

    return $this->render('journal/new.html.twig', [
        'form' => $form->createView(),
        'journal' => $journal, // Le vrai journal avec toutes ses entrées pour l'affichage
        'child' => $child,
    ]);
}

    #[Route('/index', name: 'app_journal_index', methods: ['GET'])]
    public function index(JournalRepository $journalRepository, ChildPresenceRepository $presenceRepo): Response
    {
        $presence = $presenceRepo->findOneBy([], ['arrivalTime' => 'DESC']);
        return $this->render('journal/index.html.twig', [
            'journals' => $journalRepository->findAll(),
            'presence' => $presence,
        ]);
    }

    #[Route('/{id}', name: 'app_journal_single', methods: ['GET'])]
    public function show(Journal $journal, ChildRepository $childRepository): Response
    {
    $user = $this->getUser(); 
    $child = $journal->getChild();

    // Récupère les enfants du parent connecté
    $children = [];
    if ($this->isGranted('ROLE_PARENT')) {
        $children = $childRepository->findBy(['user' => $user]);
      }

    $journals = $child->getJournals();
    $template = $this->isGranted('ROLE_PARENT') ? 'base_parent.html.twig' : 'base_admin.html.twig';

    return $this->render('journal/show.html.twig', [
        'journal' => $journal,
        'child' => $child,
        'journals' => $journals,
        'children' => $children, 
        'base_template' => $template,
    ]);
}


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

    #[Route('/journal-du-jour/{id}', name: 'app_journal_today', methods: ['GET'])]
    public function today(
    Child $child,
    JournalRepository $journalRepository,
    ChildRepository $childRepository
    ): Response {
    $user = $this->getUser();


    $journal = $journalRepository->findTodayJournalByChildAndUser($child, $user);


    return $this->render('journal/show.html.twig', [
        'journal' => $journal,
        'child' => $child,
        
    ]);
    }

}
