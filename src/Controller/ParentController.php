<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use Symfony\Bundle\SecurityBundle\Security;

final class ParentController extends AbstractController
{
    #[Route('/parent/dashboard', name: 'parent_dashboard')]
    public function dashboard(Security $security): Response
    {
        $user=$security->getUser();
        $firstName = $user->getFirstName();
        return $this->render('parent/index.html.twig', [
            'controller_name' => 'ParentController',
            'firstName' => $firstName,
        ]);
    }
}
