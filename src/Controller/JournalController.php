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
    $date = $presence->getArrivalTime()->setTime(0, 0);
    $child = $presence->getChild();

$journal = $journalRepo->findOneBy([
    'child' => $child,
    'date' => $date,
]);

if (!$journal) {
    $journal = new Journal();
    $journal->setChild($child);
    $journal->setDate(new \DateTimeImmutable());
}


$newJournal = new Journal();
$newJournal->addEntry(new JournalEntry()); 
$form = $this->createForm(JournalForm::class, $newJournal);
$form->handleRequest($request);

if ($form->isSubmitted() && $form->isValid()) {
    foreach ($newJournal->getEntries() as $entry) {
        $entry->setJournal($journal); 
        $journal->addEntry($entry);
        $dateFromForm = $form->get('date')->getData();

        if ($dateFromForm === null) {
        $dateFromForm = new \DateTimeImmutable();
    }
    }

    $entityManager->persist($journal);
    $entityManager->flush();

    return $this->redirectToRoute('app_admin_dashboard_journal');
}

return $this->render('journal/new.html.twig', [
    'form' => $form->createView(),
    'journal' => $journal, 
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
