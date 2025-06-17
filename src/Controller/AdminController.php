<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Child;
use App\Repository\ChildRepository;
use App\Repository\PlannedPresenceRepository;

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
   #[Route('/admin/dashboardChild/{id}', name: 'app_child_show', methods: ['GET'])]
    public function show(Child $child, PlannedPresenceRepository $presenceRepo): Response
    {
        $presences = $presenceRepo->findByChildOrderedByWeekday($child);
    return $this->render('Child/show.html.twig', [
        'child' => $child,
        'presences' => $presences,
    ]);
    }


}