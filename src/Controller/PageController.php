<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class PageController extends AbstractController
{
    #[Route('/', name: 'app_page')]
    public function index(): Response
    {
        $template = $this->isGranted('ROLE_ADMIN')
            ? 'page/admin_index.html.twig'
            : 'page/index.html.twig';

        return $this->render($template);
    }
}
