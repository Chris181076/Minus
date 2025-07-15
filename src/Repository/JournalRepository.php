<?php

namespace App\Repository;

use App\Entity\Journal;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Child;
use App\Entity\User;

/**
 * @extends ServiceEntityRepository<Journal>
 */
class JournalRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Journal::class);
    }
    public function findAllJournalByChild(Child $child): array
{
    return $this->createQueryBuilder('j')
        ->andWhere('j.child = :child')
        ->setParameter('child', $child)
        ->orderBy('j.date', 'DESC')
        ->getQuery()
        ->getResult();
}
public function findOneByChildAndDate(Child $child, \DateTimeInterface $date): ?Journal
{
    return $this->createQueryBuilder('j')
        ->andWhere('j.child = :child')
        ->andWhere('j.date = :date')
        ->setParameter('child', $child)
        ->setParameter('date', $date->format('Y-m-d')) // si champ Doctrine "date" est de type date
        ->getQuery()
        ->getOneOrNullResult();
}
public function findTodayJournalByChildAndUser(Child $child, User $user): ?Journal
{
    return $this->createQueryBuilder('j')
        ->join('j.child', 'c')
        ->join('c.users', 'u')
        ->andWhere('j.child = :child')
        ->andWhere('u = :user')
        ->andWhere('j.date = :today')  // si tu as un champ 'date' dans Journal
        ->setParameter('child', $child)
        ->setParameter('user', $user)
        ->setParameter('today', new \DateTimeImmutable('today'))
        ->getQuery()
        ->getOneOrNullResult();
}

//    /**
//     * @return Journal[] Returns an array of Journal objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('j')
//            ->andWhere('j.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('j.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Journal
//    {
//        return $this->createQueryBuilder('j')
//            ->andWhere('j.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
