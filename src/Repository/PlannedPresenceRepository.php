<?php

namespace App\Repository;

use App\Entity\PlannedPresence;
use App\Entity\Child;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
        ->orderBy('p.week_day', 'ASC')
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
