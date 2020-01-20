<?php

namespace App\Repository;

use App\Entity\SessionTrainer;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SessionTrainer|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionTrainer|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionTrainer[]    findAll()
 * @method SessionTrainer[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionTrainerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionTrainer::class);
    }

    // /**
    //  * @return SessionTrainer[] Returns an array of SessionTrainer objects
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
    public function findOneBySomeField($value): ?SessionTrainer
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
