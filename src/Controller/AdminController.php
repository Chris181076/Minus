<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Child;
use App\Repository\ChildRepository;
use App\Repository\PlannedPresenceRepository;
use App\Repository\JournalRepository;
use App\Entity\Journal;
use App\Repository\ChildPresenceRepository;


final class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'admin_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('admin/dashboard.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
 #[Route('/admin/dashboard/child', name: 'app_admin_dashboard_child')]
public function childDashboard(ChildRepository $childRepository): Response
{
    $children = $childRepository->findAll();

    return $this->render('admin/dashboardChild.html.twig', [
        'children' => $children,
    ]);
}
 #[Route('/admin/dashboard/journal', name: 'app_admin_dashboard_journal')]
public function journalDashboard(ChildRepository $childRepository, ChildPresenceRepository $presenceRepo): Response
{
    $children = $childRepository->findAll();
    $today = (new \DateTimeImmutable())->setTime(0, 0);
    $presences = [];
    foreach ($children as $child) {
        $presence = $presenceRepo->findOneBy([
            'child' => $child,
            'day' => $today,
        ]);
        $presences[$child->getId()] = $presence;
    }
    return $this->render('admin/dashboardChildJournal.html.twig', [
        'children' => $children,
        'presences' => $presences,
        'presence' => $presence,
    ]);
}

#[Route('/admin/dashboard/journal/{id}', name: 'app_journal_show', methods: ['GET'])]
public function childJournalDashboard(Child $child, JournalRepository $journalRepo): Response
{
    $journals = $journalRepo->findAllJournalByChild($child);

    return $this->render('journal/show.html.twig', [
        'child' => $child,
        'journals' => $journals
    ]);
}

   #[Route('/admin/dashboardChild/{id}', name: 'app_child_show', methods: ['GET'])]
    public function show(Child $child, PlannedPresenceRepository $presenceRepo): Response
    {
        $presences = $presenceRepo->findByChildOrderedByWeekday($child);
        return $this->render('Child/show_admin.html.twig', [
        'child' => $child,
        'presences' => $presences,
    ]);
    }


}