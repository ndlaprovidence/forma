<?php

namespace App\Repository;

use App\Entity\SessionLocation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method SessionLocation|null find($id, $lockMode = null, $lockVersion = null)
 * @method SessionLocation|null findOneBy(array $criteria, array $orderBy = null)
 * @method SessionLocation[]    findAll()
 * @method SessionLocation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SessionLocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SessionLocation::class);
    }

    // /**
    //  * @return SessionLocation[] Returns an array of SessionLocation objects
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
    public function findOneBySomeField($value): ?SessionLocation
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
