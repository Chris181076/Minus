<?php

namespace App\Repository;

use App\Entity\Child;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\PlannedPresence;
use App\Entity\User;
use App\Entity\Group;

/**
 * @extends ServiceEntityRepository<Child>
 */
class ChildRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Child::class);
    }

/** 
 * @return Child[] Returns an array of Child objects
 */
public function findActiveChildren(): array
{
    return $this->createQueryBuilder('c')
        ->orderBy('c.lastName', 'ASC')
        ->getQuery()
        ->getResult();
}


public function findByChildOrderedByWeekday(Child $child): array
{
    return $this->createQueryBuilder('p')
        ->where('p.child = :child')
        ->from('App\Entity\PlannedPresence', 'p')
        ->setParameter('child', $child)
        ->orderBy('p.weekDay', 'ASC')
        ->getQuery()
        ->getResult();
}

public function findByUser(User $user): array
{
    return $this->createQueryBuilder('child')
        ->andWhere(':user MEMBER OF child.users')
        ->setParameter('user', $user)
        ->getQuery()
        ->getResult();
}


//    public function findOneBySomeField($value): ?Child
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
