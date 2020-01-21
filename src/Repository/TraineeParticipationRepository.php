<?php

namespace App\Repository;

use App\Entity\TraineeParticipation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method TraineeParticipation|null find($id, $lockMode = null, $lockVersion = null)
 * @method TraineeParticipation|null findOneBy(array $criteria, array $orderBy = null)
 * @method TraineeParticipation[]    findAll()
 * @method TraineeParticipation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TraineeParticipationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TraineeParticipation::class);
    }

    // /**
    //  * @return TraineeParticipation[] Returns an array of TraineeParticipation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?TraineeParticipation
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
