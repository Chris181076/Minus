<?php

namespace App\Repository;

use App\Entity\PlannedPresence;
use App\Entity\Child;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Semainier;
use Doctrine\ORM\EntityManagerInterface;

/**
 * @extends ServiceEntityRepository<PlannedPresence>
 */
class PlannedPresenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlannedPresence::class);
    }
public function findByChildOrderedByWeekday(Child $child): array
{
    return $this->createQueryBuilder('p')
        ->andWhere('p.child = :child')
        ->setParameter('child', $child)
        ->orderBy(
            "CASE p.week_day
                WHEN 'Monday' THEN 1
                WHEN 'Tuesday' THEN 2
                WHEN 'Wednesday' THEN 3
                WHEN 'Thursday' THEN 4
                WHEN 'Friday' THEN 5
                ELSE 6
            END", 'ASC'
        )
        ->getQuery()
        ->getResult();
}
public function assignPlannedPresencesToSemainier(
    Semainier $semainier,
    array $plannedPresences,
    EntityManagerInterface $em
): void {
    foreach ($plannedPresences as $presence) {
        $presence->setSemainier($semainier);   // Lien bidirectionnel
        $semainier->addPlannedPresence($presence);// Important si tu veux garder la relation côté PHP
        $em->persist($presence);  // Nécessaire si ce sont de nouvelles entités
    }
}

public function findByChildAndWeek(Child $child, \DateTimeInterface $start, \DateTimeInterface $end): array
{
    return $this->createQueryBuilder('pp')
        ->where('pp.child = :child')
        ->andWhere('pp.week_day BETWEEN :start AND :end')
        ->setParameter('child', $child)
        ->setParameter('start', $start)
        ->setParameter('end', $end)
        ->getQuery()
        ->getResult();
}
    //    /**
    //     * @return PlannedPresence[] Returns an array of PlannedPresence objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?PlannedPresence
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
