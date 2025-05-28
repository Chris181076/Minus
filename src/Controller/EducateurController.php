<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class EducateurController extends AbstractController
{
    #[Route('/educateur/dashboard', name: 'educateur_dashboard')]
    public function dashboard(): Response
    {
        return $this->render('educateur/dashboard.html.twig', [
            'controller_name' => 'EducateurController',
        ]);
    }
}
