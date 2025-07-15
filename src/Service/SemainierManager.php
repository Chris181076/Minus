<?php
// src/Service/SemainierManager.php
namespace App\Service;

use App\Entity\Semainier;
use App\Entity\PlannedPresence;
use Doctrine\ORM\EntityManagerInterface;

class SemainierManager
{
    public function assignPlannedPresencesToSemainier(
        Semainier $semainier,
        array $plannedPresences,
        EntityManagerInterface $em
    ): void {
        foreach ($plannedPresences as $presence) {
            $presence->setSemainier($semainier);
            $semainier->addPlannedPresence($presence);
            $em->persist($presence);
        }
    }
}
