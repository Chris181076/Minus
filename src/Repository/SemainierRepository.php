<?php

namespace App\Repository;

use App\Entity\Semainier;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\ChildPresence;
use App\Entity\Child;
use App\Repository\ChildPresenceRepository;

/**
 * @extends ServiceEntityRepository<Semainier>
 */
class SemainierRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Semainier::class);
    }
public function findAllMondays(): array
{
    $all = $this->findAll();
    return array_filter($all, function($semainier) {
        return $semainier->getWeekStartDate() && $semainier->getWeekStartDate()->format('N') == 1; // 1 = lundi
    });
}
public function lastSemainier()
{
    return $this->createQueryBuilder('s')
        ->orderBy('s.week_start_date', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}
public function currentMonthSemainier(): array
{
    $firstDay = new \DateTimeImmutable('first day of this month 00:00:00');
    $lastDay  = new \DateTimeImmutable('last day of this month 23:59:59');

    return $this->createQueryBuilder('sem')
        ->andWhere('sem.week_start_date BETWEEN :start AND :end')
        ->setParameter('start', $firstDay)
        ->setParameter('end', $lastDay)
        ->orderBy('sem.week_start_date', 'DESC')
        ->setMaxResults(10)
        ->getQuery()
        ->getResult();
}
    //    /**
    //     * @return Semainier[] Returns an array of Semainier objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('s.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Semainier
    //    {
    //        return $this->createQueryBuilder('s')
    //            ->andWhere('s.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
