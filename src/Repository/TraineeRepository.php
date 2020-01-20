<?php

namespace App\Repository;

use App\Entity\Trainee;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Trainee|null find($id, $lockMode = null, $lockVersion = null)
 * @method Trainee|null findOneBy(array $criteria, array $orderBy = null)
 * @method Trainee[]    findAll()
 * @method Trainee[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TraineeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Trainee::class);
    }

    // /**
    //  * @return Trainee[] Returns an array of Trainee objects
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
    public function findOneBySomeField($value): ?Trainee
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
