<?php

namespace App\Controller;

use App\Entity\Semainier;
use App\Form\SemainierForm;
use App\Repository\SemainierRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Child;
use App\Repository\ChildRepository;
use App\Entity\ChildPresence;
use App\Repository\ChildPresenceRepository;
use App\Service\WeekHelper;

#[Route('/semainier')]
final class SemainierController extends AbstractController
{
#[Route(name: 'app_semainier_index', methods: ['GET'])]
public function index(SemainierRepository $semainierRepository, WeekHelper $weekHelper): Response
{
    [$start, $end] = $weekHelper->getWeekStartAndEnd();

    $semainiers = $semainierRepository->createQueryBuilder('s')
        ->where('s.week_start_date BETWEEN :start AND :end')
        ->setParameter('start', $start)
        ->setParameter('end', $end)
        ->orderBy('s.week_start_date', 'DESC')
        ->getQuery()
        ->getResult();

    return $this->render('semainier/index.html.twig', [
        'semainiers' => $semainiers, $semainierRepository->findAllMondays(),
        'week_days' => $weekHelper->getWeekDays(),
        'week_start' => $start,
        'week_end' => $end,
    ]);
}

    #[Route('/new', name: 'app_semainier_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $semainier = new Semainier();
        $form = $this->createForm(SemainierForm::class, $semainier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($semainier);
            $entityManager->flush();

            return $this->redirectToRoute('app_semainier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('semainier/new.html.twig', [
            'semainier' => $semainier,
            'form' => $form,
        ]);
    }

#[Route('/{id}', name: 'app_semainier_show', methods: ['GET'])]
public function show(
    Semainier $semainier,
    ChildRepository $childRepository,
    ChildPresenceRepository $childPresenceRepository, // <-- c'est bien ce repo qu'il faut
    WeekHelper $weekHelper
): Response {
    $startOfWeek = $weekHelper->getStartOfWeek($semainier->getWeekStartDate());
    $endOfWeek = $weekHelper->getEndOfWeek($semainier->getWeekStartDate());

    $children = $childRepository->findAll();
    $presencesByChild = [];
    $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    foreach ($children as $child) {
        $presences = $childPresenceRepository->findByChildAndDateRange($child, $startOfWeek, $endOfWeek);

        foreach ($presences as $presence) {
            $day = $presence->getArrivalTime()?->format('l');
            $arrival = $presence->getArrivalTime();
            $departure = $presence->getDepartureTime();

            $presencesByChild[$child->getId()]['child'] = $child;

            if ($arrival && $departure) {
                $presencesByChild[$child->getId()]['days'][$day] = sprintf(
                    '%sh%02d / %sh%02d',
                    $arrival->format('H'),
                    $arrival->format('i'),
                    $departure->format('H'),
                    $departure->format('i')
                );
            } elseif ($arrival) {
                $presencesByChild[$child->getId()]['days'][$day] = sprintf(
                    '%sh%02d / -',
                    $arrival->format('H'),
                    $arrival->format('i')
                );
            } else {
                $presencesByChild[$child->getId()]['days'][$day] = '-';
            }
        }
    }

    return $this->render('semainier/show.html.twig', [
        'semainier' => $semainier,
        'children' => $children,
        'presencesByChild' => $presencesByChild,
        'weekDays' => $weekDays,
        'startOfWeek' => $startOfWeek
    ]);
}

         

    #[Route('/{id}/edit', name: 'app_semainier_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Semainier $semainier, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SemainierForm::class, $semainier);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_semainier_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('semainier/edit.html.twig', [
            'semainier' => $semainier,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_semainier_delete', methods: ['POST'])]
    public function delete(Request $request, Semainier $semainier, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$semainier->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($semainier);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_semainier_index', [], Response::HTTP_SEE_OTHER);
    }
    
}
