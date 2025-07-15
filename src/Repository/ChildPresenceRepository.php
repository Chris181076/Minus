<?php

namespace App\Repository;

use App\Entity\ChildPresence;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Child;

/**
 * @extends ServiceEntityRepository<ChildPresence>
 */
class ChildPresenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ChildPresence::class);
    }
    public function updateChildPresence(ChildPresence $cp): void
    {
    $this->_em->persist($cp);
    $this->_em->flush();
    }
    public function findTodayPresenceForChild(int $childId, DateTimeImmutable $date): ?ChildPresence
    {
        return $this->createQueryBuilder('cp')
            ->andWhere('cp.child = :childId')
            ->andWhere('cp.day = :date')
            ->setParameter('childId', $childId)
            ->setParameter('date', $date)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function saveDeparture(ChildPresence $childPresence): void
    {
        try {
            $this->_em->persist($childPresence);
            $this->_em->flush();
        } catch (\Exception $e) {
            throw new \RuntimeException('Erreur lors de l\'enregistrement du départ: ' . $e->getMessage());
        }
    }

    public function findByChildAndDateRange(Child $child, \DateTimeInterface $start, \DateTimeInterface $end): array
    {
    return $this->createQueryBuilder('cp')
        ->andWhere('cp.child = :child')
        ->andWhere('cp.arrivalTime BETWEEN :start AND :end')
        ->setParameter('child', $child)
        ->setParameter('start', $start)
        ->setParameter('end', $end)
        ->getQuery()
        ->getResult();
    }
    public function findAllMondays(): array
    {
    return $this->createQueryBuilder('s')
    ->where('DAYOFWEEK(s.week_start_date) = 2') // 2 = lundi en MySQL
    ->orderBy('s.week_start_date', 'DESC')
    ->getQuery()
    ->getResult();
    }


    }
    //    /**
    //     * @return ChildPresence[] Returns an array of ChildPresence objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('c.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?ChildPresence
    //    {
    //        return $this->createQueryBuilder('c')
    //            ->andWhere('c.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

