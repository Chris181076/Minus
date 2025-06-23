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
