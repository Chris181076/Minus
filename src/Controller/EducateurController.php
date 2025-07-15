<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Repository\ChildRepository;
use App\Repository\ChildPresenceRepository;
use \DateTimeImmutable;
use App\Entity\Group;
use App\Repository\GroupRepository;

final class EducateurController extends AbstractController
{
    #[Route('/educateur/dashboardChild', name: 'educateur_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('educateur/dashboard.html.twig', [
            'controller_name' => 'EducateurController',
        ]);
    }
     #[Route('/educateur/dashboard/child', name: 'app_educateur_dashboard_child')]
    public function childDashboard(ChildRepository $childRepository): Response
    {
    $children = $childRepository->findAll();

    return $this->render('educateur/dashboardChild.html.twig', [
        'children' => $children,
    ]);
    }
    #[Route('/educateur/dashboard/child/journal', name: 'app_educateur_child_journal' )]
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
    return $this->render('educateur/dashboardChildJournal.html.twig', [
        'children' => $children,
        'presences' => $presences,
        'presence' => $presence,
    ]);
    }

    #[Route('/educateur/group/{name}', name: 'app_educateur_group')]
    public function showGroup(string $name, GroupRepository $groupRepo): Response
    {
    $group = $groupRepo->findOneBy(['name' => $name]);
    if (!$group) {
        throw $this->createNotFoundException("Ce groupe n'existe pas.");
    }

    return $this->render('educateur/group.html.twig', [
        'group' => $group,
        'children' => $group->getChildren(),
        'groups' => $groupRepo->findAll()
    ]);
    }
    }



