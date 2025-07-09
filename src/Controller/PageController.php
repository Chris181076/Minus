<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PageController extends AbstractController
{
    #[Route('/', name: 'app_page')]
    public function home(): Response
    {
    return $this->render('page/index.html.twig', [
        'no_sidebar' => true, // pas de sidebar sur l’accueil
    ]);
    }

    
}
