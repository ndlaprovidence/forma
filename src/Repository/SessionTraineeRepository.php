<?php

namespace App\Repository;

use App\Entity\SessionTrainee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SessionTrainee|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionTrainee|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionTrainee[]    findAll()
 * @method SessionTrainee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionTraineeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionTrainee::class);
    }

    // /**
    //  * @return SessionTrainee[] Returns an array of SessionTrainee objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?SessionTrainee
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
